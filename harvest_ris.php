<?php

// Import from RIS
// We convert to CSL, then to SQL

require_once(dirname(__FILE__) . '/ris_to_csl.php');
require_once(dirname(__FILE__) . '/csl_utils.php');

function ris_import($csl)
{
	// print_r($csl);
	
	echo csl_to_sql($csl);
}

//--------------------------------------------------------------------------------------------------
$filename = '';
if ($argc < 2)
{
	echo "Usage: import_ris.php <RIS file> \n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'ris_import');

?>
