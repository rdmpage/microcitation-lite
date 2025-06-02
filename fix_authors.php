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
	$sql = 'SELECT * FROM publications_doi where issn="0080-4703" AND year > 1923';
	
	$data = do_query($sql);
	
	foreach ($data as $obj)
	{
		echo "-- " . $obj->authors . "\n";
		
		if (preg_match('/([A-Z])([A-Z])/', $obj->authors))
		{		
			$obj->authors = preg_replace('/([A-Z])([A-Z])/', "$1 $2", $obj->authors);
			$obj->authors = preg_replace('/([A-Z])([A-Z])/', "$1 $2", $obj->authors);
		
			echo 'UPDATE publications_doi SET authors= "' . str_replace('"', '""', $obj->authors) . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
	}
}

if (1)
{
	$sql = 'SELECT * FROM publications where issn="1110-502X" AND authors LIKE "%ii%"';
	
	$data = do_query($sql);
	
	foreach ($data as $obj)
	{
		echo "-- " . $obj->authors . "\n";
		echo 'UPDATE publications SET authors= "' . str_replace('ii', 'ü', $obj->authors) . '" WHERE guid="' . $obj->guid . '";' . "\n";
	}
}


?>
