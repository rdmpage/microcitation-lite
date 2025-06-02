<?php

// AIRITI DOIs

require_once (dirname(__FILE__) . '/csl_utils.php');

require_once (dirname(__FILE__) . '/HtmlDomParser.php');

use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL 				=> $url,
	  CURLOPT_FOLLOWLOCATION	=> TRUE,
	  CURLOPT_RETURNTRANSFER 	=> TRUE,
	  
	  CURLOPT_HEADER 			=> FALSE,
	  
	  CURLOPT_SSL_VERIFYHOST	=> FALSE,
	  CURLOPT_SSL_VERIFYPEER	=> FALSE,
	  
	  CURLOPT_COOKIEJAR			=> sys_get_temp_dir() . '/cookies.txt',
	  CURLOPT_COOKIEFILE		=> sys_get_temp_dir() . '/cookies.txt',
	  
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



// 10.6693/CAR.202301_(35).0001
// 10.6693/CAR.2001.13.3

$dois=array(
'10.6693/CAR.2001.13.3',
'10.6693/CAR.202301_(35).0001',
);


$dois = array();

// 10.6693/CAR.1989.1.10

// 1989-1990

// no DOI
// 1997 9,10
// 2003 16

// DOI
// 1998 11
// 1999 12

// 2014 27


for ($year = 2010; $year <= 2017; $year++)
{
	$volume = $year - 1987;
	$num_articles = 20;

	for ($i = 1; $i <= $num_articles; $i++)
	{
		$dois[] = '10.6693/CAR.' . $year . '.' . $volume . '.' . $i;
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
	
	echo "-- $doi\n";
	
	$obj = null;
	

	$url = 'https://doi.org/' . $doi;	
	$json = get($url, 'application/vnd.citationstyles.csl+json');
	$obj = json_decode($json);
	
	//echo $json . "\n";
	
	if ($obj)	
	{
		if (0)
		{
			print_r($obj);
		}	
		
		if (isset($obj->URL) && $obj->URL == '')
		{
			unset($obj->URL);
		}

		if (isset($obj->volume) && $obj->volume == '')
		{
			if (isset($obj->issue) && $obj->issue != '')
			{
				$obj->volume = $obj->issue;
				unset($obj->issue);
			}
			
		}
		
		$obj->URL =  get_redirect('https://doi.org/' . $doi);
		
		if (isset($obj->{'page-first'}))
		{
			if (preg_match('/-(' . $obj->{'page-first'} . '-\d+)$/', $obj->URL, $m))
			{
				$obj->page = $m[1];
			}
		}
		
		$obj->{'container-title'} = 'Collection and research';
		$obj->ISSN[] = '1726-2038';

		if (0)
		{
			print_r($obj);
		}	
		
		
		$sql = csl_to_sql($obj, 'publications');		
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


?>


