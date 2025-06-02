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



if (0)
{
	$sql = 'SELECT * FROM publications where issn="2581-8686" AND year > 1962 AND spage LIKE "%–%"';
	$sql = 'SELECT * FROM publications where issn="2215-2075" AND spage LIKE "%–%"';

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
		elseif (preg_match('/(?<spage>S\d+)–(?<epage>S\d+)/u', $obj->spage, $m))
		{		
			echo 'UPDATE publications SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}

		{
			echo "-- *** No match ***\n";
		
		}
	}
}

// roman numbers
if (0)
{
	$table = 'publications_doi';
	$table = 'publications';

	$sql = 'SELECT * FROM ' . $table . ' where issn="0368-1068"  AND spage LIKE "%-%"';
	$sql = 'SELECT * FROM ' . $table . ' where issn="0093-4666"  AND spage LIKE "%-%"';
	$sql = 'SELECT * FROM ' . $table . ' where issn="2572-1410"  AND spage LIKE "%-%"';
	$sql = 'SELECT * FROM publications where issn="2215-2075" AND spage LIKE "%–%"';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$spage = $obj->spage;
	
		echo "-- " . $obj->spage . "\n";
	
		if (preg_match('/(?<spage>[ixvlcm]+)[-|–](?<epage>[ixvlcm]+)/iu', $obj->spage, $m))
		{		
			echo 'UPDATE ' . $table . ' SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		elseif (preg_match('/(?<spage>\d+)-(?<epage>[ixvlcm]+)/u', $obj->spage, $m))
		{		
			echo 'UPDATE ' . $table . ' SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		elseif (preg_match('/(?<spage>[ixvlcm]+)-(?<epage>\d+)/u', $obj->spage, $m))
		{		
			echo 'UPDATE ' . $table . ' SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		elseif (preg_match('/(?<spage>\d+F)-(?<epage>\d+F)/u', $obj->spage, $m))
		{		
			echo 'UPDATE ' . $table . ' SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		elseif (preg_match('/(?<spage>[A-Z]+\d+)-(?<epage>[A-Z]+\d+)/u', $obj->spage, $m))
		{		
			echo 'UPDATE ' . $table . ' SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		else
		{
			echo "-- *** No match ***\n";
		}
	}
}

// PDF URL 1805-5648
if (1)
{
	$table = 'publications';

	$sql = 'SELECT * FROM ' . $table . ' where issn="1805-5648"  AND epage Is NULL';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		
		echo "-- $guid\n";
		
		if (preg_match('/(?<spage>\d+)-(?<epage>\d+)(%|[a-z])/', $guid, $m))
		{
			// print_r($m);
			
			echo 'UPDATE ' . $table . ' SET epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		
		if (preg_match('/_(?<spage>\d+)-(?<epage>\d+)\.pdf/', $guid, $m))
		{
			// print_r($m);
			
			echo 'UPDATE ' . $table . ' SET epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}

		if (preg_match('/\/(?<spage>\d+)[-|_](?<epage>\d+)_/', $guid, $m))
		{
			// print_r($m);
			
			echo 'UPDATE ' . $table . ' SET epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
	}

}

?>
