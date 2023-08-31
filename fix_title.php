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

//----------------------------------------------------------------------------------------

if (0)
{
	$sql = 'SELECT * FROM publications where issn="2665-0347" AND title LIKE "%:"';

	$data = do_query($sql);

	$records = array();

	foreach ($data as $obj)
	{
		$value = $obj->title;
		$value = preg_replace('/:$/', '', $value);
		echo 'UPDATE publications SET title="' . str_replace('"', '""', $value) . '" WHERE guid="' . $obj->guid . '";' . "\n";
	}
}


//----------------------------------------------------------------------------------------

$sql = 'SELECT * FROM publications where issn="2665-0347" AND title LIKE "% / %"';

$data = do_query($sql);

$records = array();

foreach ($data as $obj)
{
	if (!isset($records[$obj->guid]))
	{
		$records[$obj->guid] = array();
	}
	$records[$obj->guid] = preg_split('/\s+\/\s+/', $obj->title);
}

print_r($records);

$ld = new Language(['en', 'es']);	

foreach ($records as $guid => $record)
{
	$title = '';
	$lang = array();
	
	foreach ($record as $value)
	{
		$value = preg_replace('/\.$/', '', $value);
		
		$language = $ld->detect($value);
		$lang[] = $language;
		
		if ($title == '')
		{
			echo 'UPDATE publications SET title="' . str_replace('"', '""', $value) . '" WHERE guid="' . $guid . '";' . "\n";
			$title = $value;
		}

		echo 'REPLACE INTO multilingual(guid, `key`, language, value) VALUES("' . $guid . '","title","' . $language . '","' . str_replace('"', '""', $value) . '");' . "\n";

	}
	
	$lang = array_unique($lang);
	if (count($lang) == 1)
	{
		echo "-- only one language, check this\n";
	}
}


?>
