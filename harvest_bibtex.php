<?php

// Import from BibTeX
// We convert to CSL, then to SQL

require_once(dirname(__FILE__) . '/bibtex_to_csl.php');
require_once(dirname(__FILE__) . '/csl_utils.php');


//--------------------------------------------------------------------------------------------------
$filename = '';
if ($argc < 2)
{
	echo "Usage: harvest_bibtex.php <BibTeX file> \n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

$bibliography = bibtex_to_csl($filename);


//print_r($bibliography);


foreach ($bibliography as $csl)
{
	echo csl_to_sql($csl, 'scratch');
}


?>
