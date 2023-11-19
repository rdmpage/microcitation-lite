<?php

$filename = 'science.jsonl';


$file = @fopen($filename, "r") or die("couldn't open $filename");		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$json = fgets($file_handle);
	
	$csl = json_decode($json);
	
	if ($csl)
	{
	
		//print_r($csl);
		
		$doi = $csl->DOI;
		$timestamp = $csl->created->timestamp;
		
		echo 'UPDATE publications_doi SET timestamp=' . $timestamp . ' WHERE doi="' . $doi . '";' . "\n";
	
	}

}

?>
