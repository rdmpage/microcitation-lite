<?php

// Take data from one table and add to another


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

$sql = 'SELECT * FROM publications_doi WHERE issn="0136-006X" and volume BETWEEN 19 AND 27 AND epage=0 and spage is not null';



$data = do_query($sql);

foreach ($data as $obj)
{
	if (isset($obj->doi)
		&& isset($obj->issn)
		&& isset($obj->volume)
		&& isset($obj->issue)
		&& isset($obj->spage)
		&& isset($obj->epage)
	) {
		$terms = array();
		
		$terms[] = 'issn="' . $obj->issn . '"';
		$terms[] = 'volume="' . $obj->volume . '"';
		//$terms[] = 'issue="' . $obj->issue . '"';
		
		$spage = 0;
		$epage = 0;
		
		
		// 4
		if (preg_match('/^(?<spage>\d)(?<epage>0[0-9]{2})$/', $obj->spage, $m))
		{
			// print_r($m);
			
			$spage = $m['spage'];
			$epage = preg_replace('/^0+/', '', $m['epage']);
		}
		
		
		if ($spage == 0)
		{
			if (preg_match('/^(?<spage>[0-9]{2})(?<epage>[0-9]{2})$/', $obj->spage, $m))
			{
				// print_r($m);
			
				$spage = $m['spage'];
				$epage = preg_replace('/^0+/', '', $m['epage']);
			}
		}
		
		/*
		// 5
		if (preg_match('/^(?<spage>[0-9]{2})(?<epage>[0-9]{3})$/', $obj->spage, $m))
		{
			// print_r($m);
			
			$spage = $m['spage'];
			$epage = preg_replace('/^0+/', '', $m['epage']);
		}

		// 6
		if (preg_match('/^(?<spage>[0-9]{3})(?<epage>[0-9]{3})$/', $obj->spage, $m))
		{
			// print_r($m);
			
			$spage = $m['spage'];
			$epage = preg_replace('/^0+/', '', $m['epage']);
		}
		*/
		
		
		if ($spage != 0)
		{
			$terms[] = 'spage="' . $spage . '"';
			$terms[] = 'epage="' . $epage . '"';			
		
			echo 'UPDATE publications SET doi="' . $obj->doi . '" WHERE ' . join(" AND ", $terms) . ';' . "\n";
		}
		
		/*
		$terms[] = 'spage="' . $obj->spage . '"';
		$terms[] = 'epage="' . $obj->epage . '"';
		
		echo 'UPDATE publications SET wikidata="' . $obj->wikidata . '" WHERE ' . join(" AND ", $terms) . ';' . "\n";
		*/
	
	
	
	}


}

?>
