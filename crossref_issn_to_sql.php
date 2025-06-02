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


$issns = array('2118-9773'); // EJT
$start 	= 2023;
$end 	= 2023;

$issns = array('1424-2818'); // Diversity
$start 	= 2020;
$end 	= 2023;

$issns = array('0511-9618');
$start 	= 2018;
$end 	= 2023;


$issns = array('1664-302X'); // Frontiers in Microbiology
$start 	= 2016;
$end 	= 2023;

$start 	= 2021;
$end 	= 2021;

// 2022
$issns = array(
'0181-1584',
'1314-4049',
'2309-608X',
'0003-6072',
'1179-3155',
'0166-0616',
);

$start 	= 2022;
$end 	= 2022;

$issns = array(
'1179-3155',
);

$start 	= 2021;
$end 	= 2021;

//----------------------------------------------------------------------------------------
// Kew Bulletin
$issns = array(
'0075-5974',
);

$start 	= 2013;
$end 	= 2013;

// Science
$issns = array(
'0036-8075',
);

$start 	= 1880;
$start 	= 1891;
$end 	= 1923;

$start 	= 1916;
$end 	= 1916;


$issns = array(
//'0031-5850', // Persoonia
'0166-0616', // Styd Myco
);
$start 	= 2023;
$end 	= 2023;


$issns = array(
//'0031-5850', // Persoonia
//'0166-0616', // Styd Myco
//'0035-418X',
//'0210-8984',
"0022-0019"
);
$start 	= 2019;
$end 	= 2023;

$start 	= 2020;
$end 	= 2024;

$start 	= 2020;
$end 	= 2023;

$start 	= 1870;
$end 	= 1890;


$issns=array(
"1323-5818"
);

$start 	= 2005;
$end 	= 2005;



//----------------------------------------------------------------------------------------

$limit = 1000;

$count = 1;

foreach ($issns as $issn)
{

	for ($year = $start; $year <= $end; $year++)
	{

		$url = 'https://api.crossref.org/works?filter=issn:' . $issn . ',from-pub-date:' . $year  . ',until-pub-date:' . ($year + 1);
		
		$url .= '&rows=' . $limit;
		
		echo "-- $url\n";
		
		$json = get($url);

		//echo $json;

		$obj = json_decode($json);

		//print_r($obj);
	
		foreach ($obj->message->items as $item)
		{
			$doi = $item->DOI;

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
	}
}

// save prefix file
file_put_contents($prefix_filename, json_encode($prefix_to_agency, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

?>
