<?php

// Microcitation search


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


$issn 	= '0035-8894';
$volume = 1;
$page 	= 15;

$issn 	= '0323-6145';
$volume = 48;
$page 	= 229;


$table = 'publications_doi';


$sql = 'SELECT * 
FROM ' . $table . '
WHERE issn="' . $issn .'" AND volume="' . $volume . '" AND '.  $page . ' BETWEEN CAST(spage AS INT) AND CAST(epage AS INT);';

// echo $sql . "\n";

$data = do_query($sql);

foreach ($data as $obj)
{
	//print_r($obj);
	
	if (isset($obj->doi))
	{
		echo $obj->doi . "\n";
	}
}

?>
