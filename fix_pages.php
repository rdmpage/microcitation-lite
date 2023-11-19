<?php

// Fixpages
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
	$sql = 'SELECT * FROM publications where issn="0453-1906" AND pdf IS NOT NULL';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$pdf = $obj->pdf;
	
		echo "-- " . $obj->pdf . "\n";
	
		// http://nh.kanagawa-museum.jp/files/data/pdf/bulletin/43/bull43_33-62_tanaka_n.pdf
		if (preg_match('/bull\d+_0?(?<spage>\d+)[-|_]0?(?<epage>\d+)_/', $obj->pdf, $m))
		{
		
			echo 'UPDATE publications SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		else
		{
			echo "-- *** No match ***\n";
		
		}
	}
}



if (1)
{
	$sql = 'SELECT * FROM publications where issn="2581-8686" AND year > 1962 AND spage LIKE "%–%"';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$spage = $obj->spage;
	
		echo "-- " . $obj->spage . "\n";
	
		if (preg_match('/(?<spage>\d+)–(?<epage>\d+)/u', $obj->spage, $m))
		{
		
			echo 'UPDATE publications SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		else
		{
			echo "-- *** No match ***\n";
		
		}
	}
}


?>
