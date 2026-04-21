<?php

// Export to RIS

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

// get CSL

$sql = 'SELECT * FROM publications_doi WHERE issn="0027-5514" AND CAST(year AS INTEGER) <= 1930';
	
$sql .= ' ORDER BY CAST(year as SIGNED), CAST(volume as SIGNED), CAST(issue AS SIGNED), CAST(spage AS SIGNED)';


$data = do_query($sql);

$count = 0;

foreach ($data as $obj)
{
	//print_r($obj);
	
	if (isset($obj->authors) && $obj->authors == "Anon.")
	{
		unset($obj->authors);
	}

	$csl = data_to_csl($obj);

	$rows = csl_to_tsv($csl, $count == 0);
	
	echo join("\n", $rows) . "\n";
	
	$count++;
}


?>
