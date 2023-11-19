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


?>
