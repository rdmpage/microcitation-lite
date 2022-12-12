<?php

// Fix IA metadata

$pdo = new PDO('sqlite:microcitation.db');

//----------------------------------------------------------------------------------------
function do_query($sql)
{
	global $pdo;
	
	$stmt = $pdo->query($sql);

	$data = array();

	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

		$item = new stdclass;
		
		$keys = array_keys($row);
	
		foreach ($keys as $k)
		{
			if ($row[$k] != '')
			{
				$item->{$k} = $row[$k];
			}
		}
	
		$data[] = $item;
	
	
	}
	
	return $data;	
}

//----------------------------------------------------------------------------------------
function post($url, $data = '')
{
	//echo $data; exit();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, 
		array(
			"Authorization: LOW fqKCdXcfuJpIuBbX:FppdAMYPByxdmEBX",
			"x-archive-interactive-priority:1",
			"Content-Type: application/x-www-form-urlencoded"
			)
		);
	
	$response = curl_exec($ch);
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		die($errorText);
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	echo $http_code  . "\n";
		
	curl_close($ch);
	
	return $response;
}

$sql = 'select * from ia_tmp 
inner join publications ON ia_tmp.ia = publications.internetarchive
where issn="0084-5604"
AND ia_tmp.title <> publications.title';


$data = do_query($sql);

$count = 1;

foreach ($data as $obj)
{
	
	$patch = array();
	
	if (isset($obj->authors))
	{
		$creator = new stdclass;
		$creator->op = "replace";
		$creator->path = "/creator";
		$creator->value = explode(";", $obj->authors);
		
		$patch[] = $creator;
	}
	
	if (isset($obj->title))
	{
		$title = new stdclass;
		$title->op = "replace";
		$title->path = "/title";
		$title->value = $obj->title;
		
		$patch[] = $title;
	}
	
	$url = 'https://archive.org/metadata/' . $obj->ia;
	
	echo $url . "\n";
	
	print_r($patch);
	
	$parameters = array(
		"-target" => "metadata",
		"-patch" => json_encode($patch)
	);
	
	$response = post($url, http_build_query($parameters));

	echo $response;
	
	// Give server a break every 10 items
	if (($count++ % 10) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}
	
	
	

}

?>
