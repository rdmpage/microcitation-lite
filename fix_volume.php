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

$sql = 'SELECT * FROM publications_doi where issn="0071-5883"';

$data = do_query($sql);

$articles = array();

foreach ($data as $obj)
{
	if (preg_match('/(?<journal>.*),\s+Volume\s+(?<volume>\d+[a-z]*),\s+(?<title>.*)\.?$/', $obj->title, $m))
	{
		//print_r($m);
		
		echo 'UPDATE publications_doi SET journal="' . $m['journal'] . '", volume="' 
		. $m['volume'] . '", title="' . $m['title'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
	}
	
	if (preg_match('/\.?$/', $obj->title, $m))
	{
		echo 'UPDATE publications_doi SET title="' . preg_replace('/\.$/', '', $obj->title) . '" WHERE guid="' . $obj->guid . '";' . "\n";
	}
	
	
}




?>
