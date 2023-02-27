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
							$csl->DOI = $meta->content;
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


$count = 1;

foreach ($dois as $doi)
{
	$obj = from_meta($doi);

	if ($obj)
	{
		//print_r($obj);
		
		if (isset($obj->DOI) && isset($obj->page))
		{
			if (preg_match('/(?<spage>\d+)-(?<epage>\d+)/', $obj->page, $m))
			{
				echo 'UPDATE publications_doi SET spage="' . $m['spage'] . '", epage="' .  $m['epage'] . '" WHERE doi="' . $obj->DOI . '";' . "\n";
			}		
		}
	
	}
	
	// Give server a break every 10 items
	if (($count++ % 3) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}	
}

?>
