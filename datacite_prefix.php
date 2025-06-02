<?php

// Get all DataCite DOIs with a given prefix

//----------------------------------------------------------------------------------------
function get($url, $format = '')
{	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	
	if ($format != '')
	{
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: " . $format));	
	}
	
	$response = curl_exec($ch);
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		die($errorText);
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	curl_close($ch);
	
	return $response;
}


//----------------------------------------------------------------------------------------


$prefix = '10.5883';
$prefix = '10.14456';


$done = false;

$url = 'https://api.datacite.org/dois?prefix=' . $prefix;

while (!$done)
{	
	$json = get($url);
	$obj = json_decode($json);
	
	// print_r($obj);
	
	if (isset($obj->data))
	{
		foreach ($obj->data as $item)
		{
			echo $item->id . "\n";
		}
	}
	
	$done = true;
	
	if (isset($obj->links))
	{
		if (isset($obj->links->next))
		{
			$url = $obj->links->next;
			$done = false;
		}
	}
	
	
}

?>
