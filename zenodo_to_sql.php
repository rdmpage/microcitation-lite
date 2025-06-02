<?php

// Import journal articles from Zenodo search

require_once (dirname(__FILE__) . '/csl_utils.php');

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type 
		);		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

// query

$journal = "Halteres";
$journal = "ANNALS OF THE UPPER SILESIAN MUSEUM IN BYTOM, ENTOMOLOGY";

$journal = "The Taxonomic Report";

$journal = "Iberus";

//$journal = "Studies and Reports. Taxonomical Series";

$journal = "Tropical Lepidoptera Research";

$journal = "Linzer biologische Beiträge";

$page = 1;
$page = 2;
$page = 3;
//$page = 4;
$page = 16;

for ($page = 1; $page < 2; $page++)
{
	
	$parameters = array(
		'page' 	=> $page,
		"size"	=> 200,
		"q"		=> 'journal.title:"' . $journal . '"'
	);
	
	
	if (1)
	{
		// communities: spira
		
		$community = 'spira';
		$community = 'opuscula-zoologica';
		
		
		$page = 1;
		
		$parameters = array(
			'page' 	=> $page,
			"size"	=> 200,
			"q"		=> 'communities:' . $community
		);
	}
	
	$url = 'https://zenodo.org/api/records?' . http_build_query($parameters);
	
	echo "-- $url\n";
	
	$json = get($url);
	
	//echo $json;
	
	$obj = json_decode($json);
	
	//print_r($obj);
	
	if (isset($obj->status))
	{
		if ($obj->status == 400)
		{
			echo "-- Done, status=400\n";
			exit();
		}
		
	}
	
	foreach ($obj->hits->hits as $hit)
	{
		//print_r($hit);
	
	
		$type = '';
	
		if (isset($hit->metadata->resource_type->type))
		{
			$type = $hit->metadata->resource_type->type;
		}
	
		if (isset($hit->metadata->resource_type->subtype))
		{
			$type = $hit->metadata->resource_type->subtype;
		}
	
		if ($type == "publication" || $type == "article")
		{
			// echo $hit->id . "\n";
			
				// CSL
			$csl = new stdclass;
		
			$csl->type = 'article-journal';
		
			$csl->title = $hit->metadata->title;
			$csl->title = html_entity_decode($csl->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$csl->DOI = $hit->doi;
			$csl->doi_agency = 'datacite';
		
			$csl->id = $hit->id;
		
			// container
			if (isset($hit->metadata->journal))
			{
				if (isset($hit->metadata->journal->title))
				{
					$csl->{'container-title'} = $hit->metadata->journal->title;
				
					switch ($csl->{'container-title'})
					{
						case 'ANNALS OF THE UPPER SILESIAN MUSEUM IN BYTOM, ENTOMOLOGY':
							$csl->ISSN[] = '0867-1966';
							break;
						
						case 'Halteres':
							$csl->ISSN[] = '0973-1555';
							break;
	
						case 'Onychium':
							$csl->ISSN[] = '1824-2669';
							break;
							
						case 'Opuscula Zoologica (Budapest)':
							$csl->ISSN[] = '0237-5419';
							break;
	
						case 'Tropical Lepidoptera Research':
							$csl->ISSN[] = '2575-9256';
							break;
						
						default:
							break;				
					}
				}
				if (isset($hit->metadata->journal->volume))
				{
					$csl->volume = $hit->metadata->journal->volume;
					
					if (preg_match('/(?<volume>\d+)\((?<issue>[^\)]+)\)/', $csl->volume, $m))
					{
						$csl->volume  = $m['volume'];
						$csl->issue   = $m['issue'];
					}
				}
				if (isset($hit->metadata->journal->pages))
				{
					$csl->page = $hit->metadata->journal->pages;
				}
			}
		
			// authors
			if (isset($hit->metadata->creators))
			{
				foreach ($hit->metadata->creators as $creator)
				{
					$author = new stdclass;
					$author->literal = $creator->name;
				
					if (preg_match('/(.*),\s+(.*)/', $author->literal, $m))
					{
						$author->literal = $m[2] . ' ' . $m[1];
					}
					$csl->author[] = $author;
				}
			}
		
			// date
			if (isset($hit->metadata->publication_date))
			{
				$csl->issued = new stdclass;			
				$csl->issued->{'date-parts'} = array();
						   
			   if (preg_match("/(?<year>[0-9]{4})-(?<month>[0-9]{1,2})-(?<day>[0-9]{1,2})/", $hit->metadata->publication_date, $matches))
			   {   
					$csl->issued->{'date-parts'}[0] = array(
						(Integer)$matches['year'],
						(Integer)$matches['month'],
						(Integer)$matches['day']
						);             
			   }
	
			   if (preg_match("/^(?<year>[0-9]{4})$/", $hit->metadata->publication_date, $matches))
			   {   
					$csl->issued->{'date-parts'}[0] = array(
						(Integer)$matches['year']
						);             
			   }
		   
			}		   
	
			// license
			if (isset($hit->metadata->license))
			{
				switch ($hit->metadata->license->id)
				{
					case 'CC-BY-4.0':
					case 'cc-by-4.0':
						$license = new stdclass;
						$license->URL = 'https://creativecommons.org/licenses/by/4.0/legalcode';
						$csl->license[] = $license;
						break;
						
					case 'cc-by-nd-4.0':
						$license = new stdclass;
						$license->URL = 'https://creativecommons.org/licenses/by-nd/4.0/legalcode';
						$csl->license[] = $license;
						break;
					
					default:
						break;
				}
		
			}
		
			// pdf
		
			// abstract
			if (isset($hit->metadata->description))
			{
				$csl->abstract = strip_tags($hit->metadata->description);
				$csl->abstract = html_entity_decode($csl->abstract, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}
			
			//print_r($csl);
			
			$sql = csl_to_sql($csl, 'publications_doi');		
			echo "\n" . $sql . "\n";
	
			
		}
	
	}
}

?>
