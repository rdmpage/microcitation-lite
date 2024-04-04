<?php

// Get list of DOIs for a DataCite publication

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

$publisher = "Société Française d'Ichtyologie";
$publisher = "Senckenberg Gesellschaft Für Naturforschung";

$issn = '2643-4776';

$done = false;
$page_number = 1;
$page_size = 1000;

$dois = array();

while (!$done)
{
	if (1)
	{
		// publisher
		$url = 'https://api.datacite.org/dois?' . urlencode('page[number]') . '=' . $page_number
			. '&' . urlencode('page[size]') . '=' . $page_size
			. '&query=' . urlencode('publisher:' . $publisher);
	}
	
	if (0)		
	{
		// issn
		$url = 'https://api.datacite.org/dois?' . urlencode('page[number]') . '=' . $page_number
			. '&' . urlencode('page[size]') . '=' . $page_size
			. '&query=' . urlencode('relatedIdentifiers.relatedIdentifier:' . $issn);		
	}
			
	$json = get($url);
	
	$obj = json_decode($json);
	
	if (!$obj)
	{
		$done = true;
	}
	
	//print_r($obj);
	
	foreach ($obj->data as $row)
	{
		$dois[] = $row->id;
	}
	
	if (isset($obj->links->next))
	{
		echo "Next = " . $obj->links->next . "\n";
		$page_number++;
	}
	else
	{
		$done = true;
	}
	

}

echo "\n" . '$dois=array(' . "\n";
foreach ($dois as $doi)
{
	echo '"' . $doi . '",' . "\n";
}
echo ");\n\n";


?>
