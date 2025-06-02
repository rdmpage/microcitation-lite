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
						case 'citation_publication_date':	
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

$urls=array (
'https://meridian.allenpress.com/scasbulletin/article/122/3/131/498821/Dr-Donald-G-ButhFebruary-23-1949-May-31-2022',
'https://meridian.allenpress.com/scasbulletin/article/122/3/135/498823/A-Brief-Survey-of-the-Fishes-Algae-and-Mega',
'https://meridian.allenpress.com/scasbulletin/article/122/3/158/498822/Acmispon-glaber-Shrub-Canopies-Facilitate-Bromus',
);

/*
$urls=array (
'https://meridian.allenpress.com/scasbulletin/article/114/3/105/477058/The-Physical-Characteristics-of-Nearshore-Rocky',
'https://meridian.allenpress.com/scasbulletin/article/114/3/123/477054/Comparison-of-the-Marine-Wood-Borer-Populations-in',
'https://meridian.allenpress.com/scasbulletin/article/114/3/129/477055/Evidence-for-Negative-Effects-of-Drought-on-Baetis',
'https://meridian.allenpress.com/scasbulletin/article/114/3/141/477057/The-Bigeye-Scad-Selar-Crumenophthalmus-Bloch-1793',
'https://meridian.allenpress.com/scasbulletin/article/114/3/149/477056/A-Flora-of-the-Ballona-Wetlands-and',
);
*/

$count = 1;

$table = 'publications_doi';

foreach ($urls as $url)
{
	echo "-- $url\n";

	$obj = from_meta($url);

	if ($obj)
	{
		$sql = csl_to_sql($obj, $table);		
		echo $sql . "\n";	
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
