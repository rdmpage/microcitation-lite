<?php

// Check Science DOIs

require_once (dirname(__FILE__) . '/csl_utils.php');
require_once (dirname(__FILE__) . '/db_to_csl.php');

$pdo = new PDO('sqlite:' . dirname(__FILE__) . '/microcitation.db');

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

// get potentially problematic DOIs


$sql = 'SELECT guid FROM publications_doi WHERE issn="0036-8075" AND (guid LIKE "%.a" OR guid LIKE "%-a")';
//$sql .= ' AND year="1903"';


$dois = array();

$data = do_query($sql);

foreach ($data as $obj)
{
	$doi = $obj->guid;
	
	$doi = preg_replace('/[-|\.][a-z]$/', '', $doi);
	
	$dois[] = $doi;
}

$dois = array_unique($dois);


/*
$dois=array(
'10.1126/science.59.1538.554',
'10.1126/science.17.439.873',
);
*/

foreach ($dois as $doi)
{
	$sql = 'SELECT * FROM publications_doi WHERE issn="0036-8075" AND guid LIKE "' . $doi . '%"';

	$data = do_query($sql);
	
	$titles = array();
	
	$good_doi = array();
	
	foreach ($data as $obj)
	{
		$title = strtolower($obj->title);
	
		if (!isset($titles[$title]))
		{
			$titles[$title] = array();
		}
		if (!isset($titles[$title][$obj->timestamp]))
		{
			$titles[$title][$obj->timestamp] = array();
		}
		
		$titles[$title][$obj->timestamp][] = $obj->doi;
		
		$good_doi[$obj->doi] = false;
	}
	
	//print_r($good_doi);
	
	foreach ($titles as &$title)
	{
		krsort($title);
	}
	
	foreach ($titles as $info)
	{
		//print_r($info);

		// DOIs for most recent timestamp
		$candidates = reset($info);
		
		if (count($candidates) == 1)
		{
			$good_doi[$candidates[0]] = true;
		}
		else
		{
			echo "\n-- Multiple possible DOIs:\n";
			
			// assume ".[a-z]" is good
			foreach ($candidates as $candidate_doi)
			{
				echo "-- " . $candidate_doi . "\n";
				if (preg_match('/\.[a-z]$/', $candidate_doi))
				{
					$good_doi[$candidate_doi] = true;
				}
			}
			echo "\n";
		}
	}	
	
	// print_r($good_doi);	
	
	foreach ($good_doi as $doi => $ok)
	{
		if (!$ok)
		{
			echo 'UPDATE publications_doi SET flag="1" WHERE guid="' . $doi . '";' . "\n";
		}
	}
}

?>
