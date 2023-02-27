<?php

// Import bibliography from CSL JSONL

//require_once(dirname(__FILE__) . '/jats_mixed_to_csl.php');
require_once(dirname(__FILE__) . '/csl_utils.php');

//--------------------------------------------------------------------------------------------------
$filename = '';
if ($argc < 2)
{
	echo "Usage: harvest_csl.php <CSL JSONL file> \n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$json = trim(fgets($file_handle));
	
	// echo $json . "\n";
	
	$csl = json_decode($json);
	
	// echo json_last_error() . "\n";
	
	// print_r($csl);
	
	if ($csl)
	{
		echo csl_to_sql($csl);
	}
}

?>
