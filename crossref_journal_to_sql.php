<?php

// Import DOIs as SQL via the CrossRef journal id (which you can get from Wikidata)

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
function doi_to_agency(&$prefix_to_agency, $prefix, $doi)
{
	$agency = '';
			
	if (isset($prefix_to_agency[$prefix]))
	{
		$agency = $prefix_to_agency[$prefix];
	}
	else
	{
		$url = 'https://doi.org/ra/' . $doi;	
		$json = get($url);
		$obj = json_decode($json);	
		if ($obj)
		{
			if (isset($obj[0]->RA))
			{
				$agency = $obj[0]->RA;		
				$prefix_to_agency[$prefix] = $agency;
			}	
		}
	}
	
	return $agency;
}


//----------------------------------------------------------------------------------------
$prefix_filename = dirname(__FILE__) . '/prefix.json';

if (file_exists($prefix_filename))
{
	$json = file_get_contents($prefix_filename);
	$prefix_to_agency = json_decode($json, true);
}
else
{
	$prefix_to_agency = array();
}

//----------------------------------------------------------------------------------------


$id = 2284; // Cryptogamie Mycologie

$url = 'http://data.crossref.org/depositorreport?pubid=J' . $id;

$text = get($url);

$rows = explode("\n", $text);

//print_r($rows);

$dois=array();

foreach ($rows as $row)
{
	if (preg_match('/^(?<doi>10\.\d+[^\s]+)\s/', $row, $m))
	{
		$dois[] = $m['doi'];
	}
}

$count = 1;

foreach ($dois as $doi)
{
	// DOI prefix
	$parts = explode('/', $doi);
	$prefix = $parts[0];
	
	// Agency lookup
	$agency = doi_to_agency($prefix_to_agency, $prefix, $doi);
	
	$doi = strtolower($doi);

	$url = 'https://doi.org/' . $doi;	
	$json = get($url, 'application/vnd.citationstyles.csl+json');
	$obj = json_decode($json);
	
	if ($obj)	
	{
		if ($agency != '')
		{
			$obj->doi_agency = $agency;
		}
	
		$sql = csl_to_sql($obj, 'publications_doi');		
		echo $sql . "\n";
	}
	
	// Give server a break every 10 items
	if (($count++ % 5) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}	
}

// save prefix file
file_put_contents($prefix_filename, json_encode($prefix_to_agency, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

?>
