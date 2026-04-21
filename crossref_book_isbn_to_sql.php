<?php

// Import DOIs as SQL via the CrossRef journal id (which you can get from Wikidata)

require_once (dirname(__FILE__) . '/csl_utils.php');

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


// Flora Capensis both older version and 1860-1933 version

$isbns = array(
'9781107051232', // 1
'9781107051263', // 2
'9781107051270', // 3

'9781107051294', // 4, part 1
'9781107051300', // 4, part 2


'9781107051317', // 5, part 1
'9781107051324', // 5, part 2
'9781107051331', // 5, part 3


'9781107051348', // 6


'9781107051355', // 7

'9781107049338', // ? older version Sistens plantas promontorii Bonae Spei Africes by Thunberg

);

// to do
// https://www.cambridge.org/core/books/icones-plantarum/lindernia-capensis/E5A3289FAD6438E30A6D2FFD3D8A0C3D
// https://www.cambridge.org/core/books/flora-australiensis/5DE292509F149EAEF07A6AB3A5F2810C

// Flora Australiensis
$isbns = array(
'9781139096072',
'9781139096027',
'9781139096065',
'9781139096010',
'9781139096058',
'9781139096034',
'9781139096041',
);

$isbns = array(
'9781139107662',
);


foreach ($isbns as $isbn)
{
	$url = 'https://api.crossref.org/works?filter=isbn:' . $isbn;
	
	$json = get($url);
	
	//echo $json;
	
	$obj = json_decode($json);
	
	
	foreach ($obj->message->items as $item)
	{
		$sql = csl_to_sql($item, 'publications_doi');	
		
		//print_r($item);
			
		echo $sql . "\n";
	}

}

// save prefix file
file_put_contents($prefix_filename, json_encode($prefix_to_agency, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

?>
