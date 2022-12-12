<?php

// Import DOIs as SQL

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

$dois=array();

$dois=array(
'10.5635/ASED.2012.28.4.230',
);


$dois = array(
'10.3406/BSEF.1996.17243',
'10.11646/zootaxa.1390.1.3',
'10.21248/contrib.entomol.23.1-4.57-69', 	// DataCite
'10.19269/sugapa2020.13(1).01', 			//mEDRA
'10.11238/mammalianscience.58.175', 		// JaLC
);


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
	
	//print_r($obj);
	//exit();
	
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
