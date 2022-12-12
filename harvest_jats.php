<?php

// Import bibliography from JATS XML

//require_once(dirname(__FILE__) . '/jats_mixed_to_csl.php');
require_once(dirname(__FILE__) . '/jats_to_csl.php');
require_once(dirname(__FILE__) . '/csl_utils.php');

//--------------------------------------------------------------------------------------------------
$filename = '';
if ($argc < 2)
{
	echo "Usage: harvest_jats.php <JATS XML file> \n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

$xml = file_get_contents($filename);

//$bibliography = jats_mixed_to_csl($xml);
$bibliography = jats_to_csl($xml);

// print_r($bibliography);

foreach ($bibliography as $csl)
{
	echo csl_to_sql($csl, 'scratch');
}

?>
