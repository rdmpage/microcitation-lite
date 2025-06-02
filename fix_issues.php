<?php

// Add/change issues, for example using J-STAGE DOIs

error_reporting(E_ALL);

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
	$sql = 'SELECT * FROM publications_doi where issn="0022-2062"';
	
	$data = do_query($sql);
	
	foreach ($data as $obj)
	{
		// 10.51033/jjapbot.30_4_3863
		if (preg_match('/jjapbot\.(\d+)_(\d+)_(\d+)/', $obj->doi, $m))
		{
			echo "-- " . $obj->doi . "\n";
			echo 'UPDATE publications_doi SET issue="' . $m[2] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
	}
}

// AND CAST(volume AS INT) BETWEEN 1 AND 10
	$sql = 'SELECT * FROM publications where issn="1807-0205" AND issue LIKE "%-%"  ORDER BY CAST(volume AS INT), CAST(spage AS INT)';

	$data = do_query($sql);
	
	$issue = array();
	
	$max = array();
	
	foreach ($data as $obj)
	{
		if (!isset($issue[$obj->volume]))
		{
			$issue[$obj->volume] = 0;
		}
		$issue[$obj->volume]++;
	
		//echo $obj->guid . ' ' . $obj->volume . ' ' . $issue[$obj->volume] . ' ' . $obj->issue . "\n";
		
		echo 'UPDATE publications SET issue="' .  $issue[$obj->volume] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		
		if (preg_match('/(\d+)-(\d+)/', $obj->issue, $m))
		{
			$max[$obj->volume] = $m[2];
		}


	}
	
	// print_r($issue);
	// print_r($max);
	
	foreach ($issue as $volume => $count)
	{
		if ($count != $max[$volume])
		{
			echo "*** $volume actual=$count, expected=" .  $max[$volume] . "\n";
		}
	}


?>
