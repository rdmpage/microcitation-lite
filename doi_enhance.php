<?php

// Enhance DOIs, e.g. by adding page numbers

require_once (dirname(__FILE__) . '/csl_utils.php');

require_once (dirname(__FILE__) . '/HtmlDomParser.php');

use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_HEADER 		=> FALSE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	  CURLOPT_COOKIEJAR=> sys_get_temp_dir() . '/cookies.txt',
	  CURLOPT_COOKIEFILE=> sys_get_temp_dir() . '/cookies.txt',
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type, 
			"Accept-Language: en-gb",
			"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 
		);
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	// echo $data;
	
	return $data;
}

//--------------------------------------------------------------------------
function get_redirect($url)
{	
	$redirect = '';
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => FALSE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	  CURLOPT_HEADER => TRUE,
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
		 
	$header = substr($data, 0, $info['header_size']);
	
	$http_code = $info['http_code'];
	
	if ($http_code == 303)
	{
		$redirect = $info['redirect_url'];
	}
	
	if ($http_code == 302)
	{
		$redirect = $info['redirect_url'];
	}

	return $redirect;
}


//----------------------------------------------------------------------------------------
function from_meta($url)
{
	$csl = null;
	
	// DOI or URL?
	if (preg_match('/^10/', $url))
	{
		$url = 'https://doi.org/'. $url;
	}
	
	$html = get($url, "text/html");	
	
	//echo $html;
		
	if ($html == '')
	{
		return $csl;
	}
	else
	{						
		$dom = HtmlDomParser::str_get_html($html);

		if ($dom)
		{
			$csl = new stdclass;
			
			$tag_citation_authors = false;

			// meta
			foreach ($dom->find('meta') as $meta)
			{
				if (isset($meta->name) && ($meta->content != ''))
				{				
					switch ($meta->name)
					{							
						case 'citation_author':
							if (!$tag_citation_authors)
							{
								$author = new stdclass;
								$author->literal = $meta->content;
								$author->literal = trim(strip_tags($author->literal));
								$author->literal = preg_replace('/[0-9,\*]/', '', $author->literal);
							
								if ($author->literal != '')
								{
									$csl->author[] = $author;
								}
							}
							break;		
							
						case 'citation_authors':
							if (!isset($csl->author))
							{
								$parts = preg_split('/,\s*/', $meta->content);
								foreach ($parts as $part)
								{
									$author = new stdclass;
									$author->literal = $part;
									$csl->author[] = $author;							
								}
								
								$tag_citation_authors = true;
							}
							break;					
																		
						case 'citation_doi':
							$csl->DOI = strtolower($meta->content);
							break;	
							
						case 'citation_title':
							$csl->title = trim($meta->content);
							$csl->title = html_entity_decode($csl->title , ENT_QUOTES | ENT_HTML5, 'UTF-8');
							$csl->title = preg_replace('/\s\s+/u', ' ', $csl->title);
							break;

						case 'citation_journal_title':
							$csl->{'container-title'} = $meta->content;
							$csl->type = 'article-journal';
							break;

						case 'citation_issn':
							$csl->ISSN[] = $meta->content;
							break;

						case 'citation_volume':
							$csl->volume = $meta->content;
							break;

						case 'citation_issue':
							$csl->issue = $meta->content;
							break;

						case 'citation_firstpage':
							$csl->{'page-first'} = $meta->content;
							$csl->{'page'} = $meta->content;
							break;

						case 'citation_lastpage':
							if (isset($csl->{'page'}))
							{
								$csl->{'page'} .= '-' . $meta->content;
							}
							break;

						case 'citation_abstract_html_url':
							$csl->URL = $meta->content;
							break;

						case 'citation_pdf_url':
							$link = new stdclass;
							$link->URL = $meta->content;
							$link->{'content-type'} = 'application/pdf';
		
							if (!isset($csl->link))
							{
								$csl->link = array();
							}
							$csl->link[] = $link;
							break;
			
						case 'citation_fulltext_html_url':
							break;
					
						case 'citation_abstract':
							$csl->abstract =  html_entity_decode($meta->content);
							break;	

						case 'citation_date':	
							$csl->issued = new stdclass;
							$csl->issued->{'date-parts'} = array();
							$csl->issued->{'date-parts'}[0] = array();
							
							if (strlen($meta->content) == 8 && is_numeric($meta->content))
							{
								$csl->issued->{'date-parts'}[0][] = (Integer)substr($meta->content, 0, 4);
								$csl->issued->{'date-parts'}[0][] = (Integer)substr($meta->content, 4, 2);
								$csl->issued->{'date-parts'}[0][] = (Integer)substr($meta->content, 6, 2);
							}
							else
							{							
								$parts = preg_split('/[-|\/]/', $meta->content);
							
								foreach ($parts as $part)
								{
									$part = preg_replace('/^0/', '', $part);
									$csl->issued->{'date-parts'}[0][] = (Integer)$part;
								}
							}
							break;
							
						case 'DC.Identifier':
							if (preg_match('/^10\.\d+\//', $meta->content))
							{
								$csl->DOI = $meta->content;
							}
							break;
							
						case 'Description':
							$csl->abstract = $meta->content;
							break;
													
						default:
							break;		
					}		
				}
			}
			
			// hacks
			
			// if we don't have date
			if (!isset($csl->issued))
			{
				if (isset($csl->DOI) && preg_match('/\/zs\.?(?<year>[0-9]{4})/', $csl->DOI, $m))
				{
					$csl->issued = new stdclass;
					$csl->issued->{'date-parts'} = array();
					$csl->issued->{'date-parts'}[0] = array();	
					$csl->issued->{'date-parts'}[0][] = (Integer)$m['year'];
				}
			}
			
			// if we don't have ISSN add it manually
			if (!isset($csl->ISSN))
			{
				if (isset($csl->{'container-title'}))
				{
					switch ($csl->{'container-title'})
					{
						case '广西植物':
							$csl->ISSN[] = '1000-3142';
							break;
					
						default:
							break;
					}
				}
			}
			
			// DOI-specific things
			if (isset($csl->DOI) && preg_match('/10.11833/', $csl->DOI))
			{
				foreach ($dom->find('footer a') as $a)
				{
					$link = new stdclass;
					$link->URL = 'https://zlxb.zafu.edu.cn' . $a->href;
					$link->{'content-type'} = 'application/pdf';

					if (!isset($csl->link))
					{
						$csl->link = array();
					}
					$csl->link[0] = $link;
				}
				
				// English title
				foreach ($dom->find('section[class=articleEn] h2') as $h2)
				{
					$csl->multi = new stdclass;
					$csl->multi->_key = new stdclass;
					$csl->multi->_key->title = new stdclass;
					
					$language = 'zh';
					$csl->multi->_key->title->{$language} = $csl->title;
				
					$language = 'en';
					$csl->multi->_key->title->{$language} = $h2->plaintext;
					
				}
			}		
		}			
	}
	
	if (!isset($csl->title))
	{
		$csl = null;
	}
		
	return $csl;
}



$dois=array();

$dois=array(

'10.13158/heia.24.1.2011.59',
'10.13158/heia.24.1.2011.147',
'10.13158/heia.24.1.2011.151',
'10.13158/heia.24.1.2011.53',
'10.13158/heia.24.1.2011.167',
'10.13158/heia.24.1.2011.145',
'10.13158/heia.24.1.2011.163',
'10.13158/heia.24.1.2011.159',
'10.13158/heia.24.1.2011.65',
'10.13158/heia.24.1.2011.33',
'10.13158/heia.24.1.2011.121',
'10.13158/heia.28.1.2015.28',
'10.13158/heia.28.1.2015.212',
'10.13158/heia.29.1.2016.108',
'10.13158/heia.29.2.2016.668',
'10.13158/heia.29.2.2016.383',
'10.13158/heia.29.2.2016.610',
'10.13158/heia.31.1.2018.630',
'10.13158/heia.35.1.2022.1',
'10.13158/heia.35.1.2022.105',
'10.13158/heia.35.1.2022.115',
'10.13158/heia.35.1.2022.138',
'10.13158/heia.35.1.2022.163',
'10.13158/heia.35.1.2022.131',
'10.13158/heia.35.1.2022.174',
'10.13158/heia.35.1.2022.177',
'10.13158/heia.35.1.2022.155',
'10.13158/heia.35.1.2022.186',
'10.13158/heia.35.1.2022.22',
'10.13158/heia.35.1.2022.32',
'10.13158/heia.35.1.2022.41',
'10.13158/heia.35.1.2022.6',
'10.13158/heia.35.1.2022.61',
'10.13158/heia.35.1.2022.193',
'10.13158/heia.35.2.2022.395',
'10.13158/heia.35.2.2022.420',
'10.13158/heia.35.2.2022.443',
'10.13158/heia.35.2.2022.462',
'10.13158/heia.35.2.2022.475',
'10.13158/heia.35.2.2022.494',
'10.13158/heia.35.2.2022.510',
'10.13158/heia.35.2.2022.541',
'10.13158/heia.35.2.2022.564',
'10.13158/heia.35.2.2022.613',
'10.13158/heia.35.2.2022.621',
'10.13158/heia.35.2.2022.630',
'10.13158/heia.35.2.2022.636',
'10.13158/heia.35.2.2022.656',
'10.13158/heia.35.2.2022.664',
'10.13158/heia.35.2.2022.670',
'10.13158/heia.35.2.2022.675',
'10.13158/heia.35.2.2022.682',
'10.13158/heia.35.2.2022.687',
'10.13158/heia.35.2.2022.689',
'10.13158/heia.35.2.2022.690',
'10.13158/heia.35.2.2022.467',

);

$dois=array(

'10.1071/mr04014',
'10.1071/mr04003',
'10.1071/mr04013',
'10.1071/mr04015',
'10.1071/mr04002',
'10.1071/mr04001',
'10.1071/mr04006',
'10.1071/mr04004',
'10.1071/mr04012',
'10.1071/mr03013',
'10.1071/mr03012',
'10.1071/mr03010',
'10.1071/mr04008',
'10.1071/mr04011',
'10.1071/mrauth2004',
'10.1071/mr04009',
'10.1071/mr03009',
'10.1071/mrv24n2_br',
'10.1071/mr04007',
'10.1071/mr04005',
'10.1071/mr04010',
'10.11646/mr.26.1',
'10.11646/mr.26.3',
'10.11646/mr.26.2',
'10.11646/mr.27.2',
'10.11646/mr.27.3',
'10.11646/mr.27.1',
'10.11646/mr.28.3',
'10.11646/mr.28.2',
'10.11646/mr.28.1',
'10.11646/mr.29.2',
'10.11646/mr.29.1',
'10.11646/mr.29.3',
'10.11646/mr.30.3',
'10.11646/mr.30.1',
'10.11646/mr.30.2',
'10.11646/mr.31.2',
'10.11646/mr.31.3',
'10.11646/mr.31.1',
'10.11646/mr.32.2',
'10.11646/mr.32.3',
'10.11646/mr.32.1',

'10.1071/mr02015',
'10.1071/mr02016',
'10.1071/mr03002',
'10.1071/mr03003',
'10.1071/mr03001',
'10.1071/mr02018',

);

$dois=array(
'10.3120/0024-9637-68.3.257',
'10.3120/0024-9637-68.3.283',
'10.3120/0024-9637-68.4.339',
'10.3120/0024-9637-68.4.512',
'10.3120/0024-9637-68.4.511',
'10.3120/0024-9637-68.4.514',
'10.3120/0024-9637-68.4.515',
'10.3120/0024-9637-68.4.517',
'10.3120/0024-9637-68.4.343',
'10.3120/0024-9637-68.4.360',
'10.3120/0024-9637-68.4.366',
'10.3120/0024-9637-68.4.377',
'10.3120/0024-9637-68.4.388',
'10.3120/0024-9637-68.4.406',
'10.3120/0024-9637-68.4.416',
'10.3120/0024-9637-68.4.425',
'10.3120/0024-9637-68.4.434',
'10.3120/0024-9637-68.4.443',
'10.3120/0024-9637-68.4.450',
'10.3120/0024-9637-68.4.461',
'10.3120/0024-9637-68.4.473',
'10.3120/0024-9637-68.4.487',
'10.3120/0024-9637-69.1.1',
'10.3120/0024-9637-69.1.135',
'10.3120/0024-9637-69.1.138',
'10.3120/0024-9637-69.1.102',
'10.3120/0024-9637-69.1.111',
'10.3120/0024-9637-69.1.16',
'10.3120/0024-9637-69.1.3',
'10.3120/0024-9637-69.1.24',
'10.3120/0024-9637-69.1.4',
'10.3120/0024-9637-69.1.30',
'10.3120/0024-9637-69.1.54',
'10.3120/0024-9637-69.1.6',
'10.3120/0024-9637-69.1.62',
'10.3120/0024-9637-69.1.74',
'10.3120/0024-9637-69.1.88',
'10.3120/0024-9637-69.1.95',
'10.3120/0024-9637-69.1.42',
'10.3120/0024-9637-69.2.139',
'10.3120/0024-9637-69.2.191',
'10.3120/0024-9637-69.2.181',
'10.3120/0024-9637-69.2.202',
'10.3120/0024-9637-69.3.203',
'10.3120/0024-9637-69.3.205',
'10.3120/0024-9637-69.3.207',
'10.3120/0024-9637-69.3.210',
'10.3120/0024-9637-69.3.225',
'10.3120/0024-9637-69.3.286',
'10.3120/0024-9637-69.3.235',
'10.3120/0024-9637-69.3.252',
'10.3120/0024-9637-69.3.263',
'10.3120/0024-9637-69.4.299',
'10.3120/0024-9637-69.4.300',
'10.3120/0024-9637-69.4.302',
'10.3120/0024-9637-69.4.303',
'10.3120/0024-9637-69.4.304',
'10.3120/0024-9637-69.4.327',
'10.3120/0024-9637-69.4.341',
'10.3120/0024-9637-69.4.349',
'10.3120/0024-9637-69.4.359',
'10.3120/0024-9637-69.4.360',
'10.3120/0024-9637-69.4.362',
'10.3120/0024-9637-69.4.363',
'10.3120/0024-9637-69.4.364',
'10.3120/0024-9637-69.4.366',
'10.3120/0024-9637-70.1.1',
'10.3120/0024-9637-70.1.11',
'10.3120/0024-9637-70.1.14',
'10.3120/0024-9637-70.1.13',
'10.3120/0024-9637-70.1.16',
'10.3120/0024-9637-70.1.23',
'10.3120/0024-9637-70.1.4',
'10.3120/0024-9637-70.1.67',
'10.3120/0024-9637-70.1.9',
'10.3120/0024-9637-70.2.104',
'10.3120/0024-9637-70.2.73',
'10.3120/0024-9637-70.2.114',
'10.3120/0024-9637-70.2.74',
'10.3120/0024-9637-70.2.77',
'10.3120/0024-9637-70.2.79',
'10.3120/0024-9637-70.2.90',
'10.3120/0024-9637-70.2.97',
'10.3120/0024-9637-70.3.119',
'10.3120/0024-9637-70.3.121',
'10.3120/0024-9637-70.3.123',
'10.3120/0024-9637-70.3.126',
'10.3120/0024-9637-70.3.138',
'10.3120/0024-9637-70.3.151',
'10.3120/0024-9637-70.3.158',
'10.3120/0024-9637-70.3.172',
'10.3120/0024-9637-70.3.182',
'10.3120/0024-9637-70.3.185',
'10.3120/0024-9637-70.3.189',
'10.3120/0024-9637-70.4.195',
'10.3120/0024-9637-70.4.196',
'10.3120/0024-9637-70.4.232',
'10.3120/0024-9637-70.4.242',
'10.3120/0024-9637-70.4.248',
'10.3120/0024-9637-70.4.256',
'10.3120/0024-9637-70.4.257',
'10.3120/0024-9637-70.4.259',
'10.3120/0024-9637-70.4.197',
'10.3120/0024-9637-70.4.210',
'10.3120/0024-9637-70.4.225',
'10.3120/0024-9637-70.4.260',
);

/*
$dois=array(
'10.3120/0024-9637-70.3.185',
);
*/

$dois=array(
/*
'10.60024/zenodo.4268547',
'10.60024/zenodo.4268545',
'10.60024/zenodo.4268555',
'10.60024/zenodo.4268551',
'10.60024/zenodo.4268553',
'10.60024/zenodo.4268561',*/
'10.60024/zenodo.4268549',
'10.60024/zenodo.4268557',
'10.60024/zenodo.4268559',
'10.60024/zenodo.5703200',
'10.60024/zenodo.5703204',
'10.60024/zenodo.5703198',
'10.60024/zenodo.5703206',
'10.60024/zenodo.5703196',
'10.60024/zenodo.5703209',
'10.60024/zenodo.5703202',
'10.60024/odon.v51i3-4.a1',
'10.60024/odon.v51i3-4.a2',
'10.60024/odon.v51i3-4.a4',
'10.60024/odon.v51i3-4.a5',
'10.60024/odon.v51i3-4.a3',
'10.60024/odon.v51i3-4.a6',
'10.60024/odon.v52i1-2.a1',
'10.60024/odon.v52i1-2.a5',
'10.60024/odon.v52i1-2.a4',
'10.60024/odon.v52i1-2.a3',
'10.60024/odon.v52i1-2.a6',
'10.60024/odon.v52i1-2.a2',
'10.60024/odon.v52i1-2.a8',
'10.60024/odon.v52i1-2.a7',
'10.60024/odon.v51i1-2.a7',
'10.60024/odon.v51i1-2.a4',
'10.60024/odon.v51i1-2.a1',
'10.60024/odon.v51i1-2.a9',
'10.60024/odon.v51i1-2.a2',
'10.60024/odon.v51i1-2.a5',
'10.60024/odon.v51i1-2.a8',
'10.60024/odon.v51i1-2.a6',
'10.60024/odon.v51i1-2.a3',
'10.60024/odon.v52i3-4.a1',
'10.60024/odon.v52i3-4.a10',
'10.60024/odon.v52i3-4.a3',
'10.60024/odon.v52i3-4.a11',
'10.60024/odon.v52i3-4.a2',
'10.60024/odon.v52i3-4.a7',
'10.60024/odon.v52i3-4.a4',
'10.60024/odon.v52i3-4.a5',
'10.60024/odon.v52i3-4.a6',
'10.60024/odon.v52i3-4.a8',
'10.60024/odon.v52i3-4.a9',
'10.60024/odon.v53i1-2.a1',
'10.60024/odon.v53i1-2.a10',
'10.60024/odon.v53i1-2.a2',
'10.60024/odon.v53i1-2.a3',
'10.60024/odon.v53i1-2.a4',
'10.60024/odon.v53i1-2.a5',
'10.60024/odon.v53i1-2.a6',
'10.60024/odon.v53i1-2.a7',
'10.60024/odon.v53i1-2.a8',
'10.60024/odon.v53i1-2.a9',
'10.60024/odon.v53i3-4.a1',
'10.60024/odon.v53i3-4.a5',
'10.60024/odon.v53i3-4.a6',
'10.60024/odon.v53i3-4.a10',
'10.60024/odon.v53i3-4.a2',
'10.60024/odon.v53i3-4.a11',
'10.60024/odon.v53i3-4.a3',
'10.60024/odon.v53i3-4.a4',
'10.60024/odon.v53i3-4.a7',
'10.60024/odon.v53i3-4.a9',
'10.60024/odon.v53i3-4.a8',
);

$dois=array(

'10.4081/memoriesei.2017.3',
'10.4081/memoriesei.2017.57',
'10.4081/memoriesei.2017.91',
'10.4081/memoriesei.2017.137',
'10.4081/memoriesei.2017.155',
'10.4081/memoriesei.2018.111',
'10.4081/memoriesei.2018.45',
'10.4081/memoriesei.2018.83',
'10.4081/memoriesei.2018.3',
'10.4081/memoriesei.2019.3',
'10.4081/memoriesei.2020.311',
'10.4081/memoriesei.2020.303',
'10.4081/memoriesei.2020.279',
'10.4081/memoriesei.2020.271',
'10.4081/memoriesei.2020.261',
'10.4081/memoriesei.2020.249',
'10.4081/memoriesei.2020.211',
'10.4081/memoriesei.2020.203',
'10.4081/memoriesei.2020.191',
'10.4081/memoriesei.2020.145',
'10.4081/memoriesei.2020.105',
'10.4081/memoriesei.2020.127',
'10.4081/memoriesei.2020.83',
'10.4081/memoriesei.2020.47',
'10.4081/memoriesei.2020.15',
'10.4081/memoriesei.2020.7',
'10.4081/memoriesei.2020.5',
'10.4081/memoriesei.2020.171',
);

$dois=array(

'10.4081/bollettinosei.2012.3',
'10.4081/bollettinosei.2012.7',
'10.4081/bollettinosei.2012.12',
'10.4081/bollettinosei.2012.19',
'10.4081/bollettinosei.2012.23',
'10.4081/bollettinosei.2012.28',
'10.4081/bollettinosei.2012.31',
'10.4081/bollettinosei.2012.44',
'10.4081/bollettinosei.2012.99',
'10.4081/bollettinosei.2012.107',
'10.4081/bollettinosei.2012.117',
'10.4081/bollettinosei.2012.125',
'10.4081/bollettinosei.2012.140',
'10.4081/bollettinosei.2012.136',
'10.4081/bollettinosei.2012.a',
'10.4081/bollettinosei.2013.3',
'10.4081/bollettinosei.2013.9',
'10.4081/bollettinosei.2013.27',
'10.4081/bollettinosei.2013.33',
'10.4081/bollettinosei.2013.48',
'10.4081/bollettinosei.2013.558',
'10.4081/bollettinosei.2013.51',
'10.4081/bollettinosei.2013.59',
'10.4081/bollettinosei.2013.69',
'10.4081/bollettinosei.2013.87',
'10.4081/bollettinosei.2013.91',
'10.4081/bollettinosei.2013.v',
'10.4081/bollettinosei.2013.99',
'10.4081/bollettinosei.2013.103',
'10.4081/bollettinosei.2013.117',
'10.4081/bollettinosei.2013.121',
'10.4081/bollettinosei.2013.129',
'10.4081/bollettinosei.2013.141',
'10.4081/bollettinosei.2013.ix',
'10.4081/bollettinosei.2014.51',
'10.4081/bollettinosei.2014.87',
'10.4081/bollettinosei.2014.92',
'10.4081/bollettinosei.2014.111',
'10.4081/bollettinosei.2014.99',
'10.4081/bollettinosei.2014.113',
'10.4081/bollettinosei.2014.129',
'10.4081/bollettinosei.2012.51',
'10.4081/bollettinosei.2012.71',
'10.4081/bollettinosei.2012.79',
'10.4081/bollettinosei.2012.87',
'10.4081/bollettinosei.2012.89',
'10.4081/bollettinosei.2012.93',
'10.4081/bollettinosei.2015.3',
'10.4081/bollettinosei.2015.31',
'10.4081/bollettinosei.2015.35',
'10.4081/bollettinosei.2015.39',
'10.4081/bollettinosei.2015.43',
'10.4081/bollettinosei.2015.48',
'10.4081/bollettinosei.2015.75',
'10.4081/bollettinosei.2015.79',
'10.4081/bollettinosei.2015.85',
'10.4081/bollettinosei.2015.51',
'10.4081/bollettinosei.2015.89',
'10.4081/bollettinosei.2015.91',
'10.4081/bollettinosei.2015.ii',
'10.4081/bollettinosei.2015.135',
'10.4081/bollettinosei.2015.99',
'10.4081/bollettinosei.2015.113',
'10.4081/bollettinosei.2015.137',
'10.4081/bollettinosei.2015.141',
'10.4081/bollettinosei.2015.iii',
'10.4081/bollettinosei.2015.i',
'10.4081/bollettinosei.2016.3',
'10.4081/bollettinosei.2016.33',
'10.4081/bollettinosei.2016.44',
'10.4081/bollettinosei.2016.41',
'10.4081/bollettinosei.2016.51',
'10.4081/bollettinosei.2016.57',
'10.4081/bollettinosei.2016.71',
'10.4081/bollettinosei.2016.63',
'10.4081/bollettinosei.2016.75',
'10.4081/bollettinosei.2016.83',
'10.4081/bollettinosei.2016.90',
'10.4081/bollettinosei.2016.91',
'10.4081/bollettinosei.2016.115',
'10.4081/bollettinosei.2016.121',
'10.4081/bollettinosei.2016.99',
'10.4081/bollettinosei.2016.141',
'10.4081/bollettinosei.2016.142',
'10.4081/bollettinosei.2017.33',
'10.4081/bollettinosei.2017.3',
'10.4081/bollettinosei.2017.45',
'10.4081/bollettinosei.2017.51',
'10.4081/bollettinosei.2017.59',
'10.4081/bollettinosei.2017.67',
'10.4081/bollettinosei.2017.55',
'10.4081/bollettinosei.2017.75',
'10.4081/bollettinosei.2017.93',
'10.4081/bollettinosei.2017.99',
'10.4081/bollettinosei.2017.131',
'10.4081/bollettinosei.2017.105',
'10.4081/bollettinosei.2017.119',
'10.4081/bollettinosei.2017.135',
'10.4081/bollettinosei.2017.137',
'10.4081/bollettinosei.2018.47',
'10.4081/bollettinosei.2018.31',
'10.4081/bollettinosei.2018.21',
'10.4081/bollettinosei.2018.3',
'10.4081/bollettinosei.2018.41',
'10.4081/bollettinosei.2014.137',
'10.4081/bollettinosei.2014.v',
'10.4081/bollettinosei.2014.143',
'10.4081/bollettinosei.2014.83',
'10.4081/bollettinosei.2014.iv',
'10.4081/bollettinosei.2014.31',
'10.4081/bollettinosei.2014.7',
'10.4081/bollettinosei.2014.i',
'10.4081/bollettinosei.2014.47',
'10.4081/bollettinosei.2014.41',
'10.4081/bollettinosei.2014.17',
'10.4081/bollettinosei.2014.11',
'10.4081/bollettinosei.2014.3',
'10.4081/bollettinosei.2018.97',
'10.4081/bollettinosei.2018.55',
'10.4081/bollettinosei.2018.93',
'10.4081/bollettinosei.2018.87',
'10.4081/bollettinosei.2018.81',
'10.4081/bollettinosei.2018.145',
'10.4081/bollettinosei.2018.139',
'10.4081/bollettinosei.2018.123',
'10.4081/bollettinosei.2018.111',
'10.4081/bollettinosei.2018.101',
'10.4081/bollettinosei.2018.113',
'10.4081/bollettinosei.2018.127',
'10.4081/bollettinosei.2018.107',
'10.4081/bollettinosei.2019.33',
'10.4081/bollettinosei.2019.25',
'10.4081/bollettinosei.2019.13',
'10.4081/bollettinosei.2019.45',
'10.4081/bollettinosei.2019.7',
'10.4081/bollettinosei.2019.3',
'10.4081/bollettinosei.2019.93',
'10.4081/bollettinosei.2019.65',
'10.4081/bollettinosei.2019.51',
'10.4081/bollettinosei.2019.61',
'10.4081/bollettinosei.2019.143',
'10.4081/bollettinosei.2019.141',
'10.4081/bollettinosei.2019.133',
'10.4081/bollettinosei.2019.125',
'10.4081/bollettinosei.2019.99',
'10.4081/bollettinosei.2019.129',
'10.4081/bollettinosei.2020.45',
'10.4081/bollettinosei.2020.43',
'10.4081/bollettinosei.2020.41',
'10.4081/bollettinosei.2020.37',
'10.4081/bollettinosei.2020.25',
'10.4081/bollettinosei.2020.17',
'10.4081/bollettinosei.2020.9',
'10.4081/bollettinosei.2020.3',

);

$dois=array(


'10.3160/0038-3872-118.1.1',
'10.3160/0038-3872-118.1.21',
'10.3160/0038-3872-118.1.42',
'10.3160/0038-3872-118.1.58',
'10.3160/0038-3872-118.1.71',
'10.3160/0038-3872-118.1.76',
'10.3160/soca',
'10.3160/0038-3872-118.2.102',
'10.3160/0038-3872-118.2.109',
'10.3160/0038-3872-118.2.111',
'10.3160/0038-3872-118.2.79',
'10.3160/0038-3872-118.2.87',
'10.3160/0038-3872-118.3.139',
'10.3160/0038-3872-118.3.158',
'10.3160/0038-3872-118.3.173',
'10.3160/0038-3872-118.3.200',
'10.3160/0038-3872-119.1.1',
'10.3160/0038-3872-119.1.18',
'10.3160/0038-3872-119.1.35',
'10.3160/0038-3872-119.1.6',
'10.3160/0038-3872-119.2.49',
'10.3160/0038-3872-119.2.55',
'10.3160/0038-3872-119.3.65',
'10.3160/0038-3872-119.3.68',
'10.3160/0038-3872-120.1.1',
'10.3160/0038-3872-120.1.26',
'10.3160/0038-3872-120.2.49',
'10.3160/0038-3872-120.2.88',
'10.3160/0038-3872-120.2.59',
'10.3160/0038-3872-120.2.64',
'10.3160/0038-3872-120.3.132',
'10.3160/0038-3872-120.3.128',
'10.3160/0038-3872-120.3.99',
'10.3160/0038-3872-121.1.1',
'10.3160/0038-3872-121.1.27',
'10.3160/0038-3872-121.1.34',
'10.3160/0038-3872-121.2.41',
'10.3160/0038-3872-121.2.88',
'10.3160/0038-3872-121.3.139',
'10.3160/soca-2022-00001',
'10.3160/soca-2022-00003',
'10.3160/0038-3872-122.1.1',
'10.3160/0038-3872-122.1.19',
'10.3160/0038-3872-122.1.33',
'10.3160/0038-3872-122.1.51',
'10.3160/0038-3872-122.2.101',
'10.3160/0038-3872-122.2.122',
'10.3160/0038-3872-122.2.57',
'10.3160/0038-3872-122.2.62',
'10.3160/0038-3872-122.2.80',
'10.3160/0038-3872-123.1.1',
'10.3160/0038-3872-123.1.10',
'10.3160/0038-3872-123.1.25',
'10.3160/0038-3872-123.1.53',
'10.3160/0038-3872-123.2.61',
'10.3160/0038-3872-123.2.85',
'10.3160/0038-3872-123.2.101',
'10.3160/0038-3872-123.2.79',
'10.3160/0038-3872-123.2.96',
'10.3160/0038-3872-123.3.105',
'10.3160/0038-3872-123.3.135',
);


$count = 1;

foreach ($dois as $doi)
{
	echo "-- $doi\n";

	$obj = from_meta($doi);

	if ($obj)
	{
		// print_r($obj);
		
		
		if (isset($obj->DOI) && isset($obj->page))
		{
			if (preg_match('/(?<spage>\d+)-(?<epage>\d+)/', $obj->page, $m))
			{
				echo 'UPDATE publications_doi SET spage="' . $m['spage'] . '", epage="' .  $m['epage'] . '" WHERE doi="' . $obj->DOI . '";' . "\n";
			}		
		}

		if (isset($obj->DOI) && isset($obj->volume))
		{
			echo 'UPDATE publications_doi SET volume="' . $obj->volume . '" WHERE doi="' . $obj->DOI . '";' . "\n";
		}

		if (isset($obj->DOI) && isset($obj->issue))
		{
			echo 'UPDATE publications_doi SET issue="' . $obj->issue . '" WHERE doi="' . $obj->DOI . '";' . "\n";
		}

		
		if (isset($obj->DOI) && isset($obj->title))
		{
			//echo 'UPDATE publications_doi SET title="' . str_replace("'", "''", $obj->title) . '" WHERE doi="' . $obj->DOI . '";' . "\n";
		}
		
	
	}
	
	// Give server a break every 2 items
	if (($count++ % 1) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}	
}

?>
