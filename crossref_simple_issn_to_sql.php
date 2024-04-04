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
$end 	= 1923;

//$start 	= 1916;
//$end 	= 1916;

// ZooKeys
$issns = array(
'1313-2970',
);
$start 	= 2008;
$end 	= 2023;


// Index Fungorum update

$issns = array(
//"2077-7019",
//"2309-608X",
"1179-3155",
"1560-2745",
"2229-2225",
"1672-6472",
"0802-8966",
"2049-2375",
"2651-1339",
"2465-4973",
"1424-2818",
"0027-5514",
"0378-2697",
"0022-2011",
"0376-5156",
"1664-302X",
"2589-3823",
"0394-9486",
"0187-7151",
"1314-4049",
"0007-2745",
"2634-7768",
"2785-4124",
"0093-4666",
"0082-0598",
"2079-7737",
"2653-4649",
"2165-0497",
"2150-1203",
"1617-416X",
"0932-4739",
"0031-5850",
"1340-3540",
"0102-3306",
"0035-6441",
"0085-4417",
"0343-8651",
"0166-0616",
"2235-2988",
"0029-5035",
"1229-8093",
"1517-8382",
"0026-2617",
"0075-5974",
"0370-6583",
"0107-055X",
"0024-2829",
"2118-9773",
"0800-1820",
"1466-5026",
"1314-2828",
"0181-1584",
"2210-6340",
"0379-5179",
"1437-4781",
"0026-3648",
"1471-2164",
"0028-825X",
"0372-333X",
"0015-5632",
"1328-4401",
"1999-3110",
"2075-1729",
"0453-3402",
"2382-9664",
"0006-8055",
"1430-595X",
"1436-2317",
"0018-0971",
"1471-2180",
"2612-7512",
"2223-7747",
"2100-0840",
"2076-0817",
"0933-7407",
"0170-110X",
"0236-6495",
"1771-754X",
"1460-2709",
"0749-503X",
"1664-8021",
"0901-7593",
"1477-2000",
"1999-4907",
"0944-5013",
"1560-3695",
"0395-7527",
"1347-6270",
);

$issns=array(
//"1999-4907",
"0093-4666",
);

$start 	= 2022;
$end 	= 2022;





//----------------------------------------------------------------------------------------

$count = 1;

$limit = 1000;

foreach ($issns as $issn)
{

	for ($year = $start; $year <= $end; $year++)
	{
	
		$done = false;
		
		$cursor = '*';
		$result_count = 0;
		
		while (!$done)
		{

			$url = 'https://api.crossref.org/works?filter=issn:' . $issn . ',from-pub-date:' . $year  . ',until-pub-date:' . ($year + 1);
			$url .= '&cursor=' . $cursor;
		
			echo "-- $url\n";
		
			$json = get($url);

			//echo $json;
			
			//exit();

			$obj = json_decode($json);
			
			$cursor = $obj->message->{'next-cursor'};
			
			echo "-- $cursor\n";
			
			$result_count += count($obj->message->items);
			
			echo "-- $result_count\n";
			
			if ($result_count >= $obj->message->{'total-results'})
			{
				$done = true;
			}			

			//print_r($obj);
		
			foreach ($obj->message->items as $item)
			{
				$doi = $item->DOI;

				// DOI prefix
				$parts = explode('/', $doi);
				$prefix = $parts[0];
	
				// Agency lookup
				$agency = doi_to_agency($prefix_to_agency, $prefix, $doi);
		
				$sql = csl_to_sql($item, 'publications_doi');		
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
