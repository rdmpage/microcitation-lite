<?php

// Fix multilingual
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
use LanguageDetection\Language;

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


$sql = 'SELECT * FROM multilingual where guid like "https://www.lillo.org.ar%"';

$data = do_query($sql);

$records = array();

foreach ($data as $obj)
{
	if (!isset($records[$obj->guid]))
	{
		$records[$obj->guid] = array();
	}
	$records[$obj->guid][$obj->language] = $obj->value;
}


$ld = new Language(['en', 'la', 'es', 'de']);	

foreach ($records as $guid => $record)
{
	if (count($record) == 1)
	{
		print_r($record);
		
		$value = array_values($record)[0];
		$lan = array_keys($record)[0];
											
		$language = $ld->detect($value);
		//echo $language . "\n";
		
		if ($lan != $language)
		{
			echo 'DELETE FROM multilingual WHERE guid="' . $guid . '";' . "\n";
			echo 'REPLACE INTO multilingual(guid, `key`, language, value) VALUES("' . $guid . '","title","' . $language . '","' . str_replace('"', '""', $value) . '");' . "\n";
		
		}
		
		

	}
}
?>
