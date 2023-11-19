<?php

// Dump data to files for archiving

error_reporting(E_ALL);

$pdo = new PDO('sqlite:microcitation.db');

require_once (dirname(__FILE__) . '/db_to_csl.php');

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


$datadir = dirname(__FILE__) . '/data';

$table = 'publications';

$issns = array(
//	['0438-0479'],
//	['0453-1906'],
	['0071-1268'],
);


foreach ($issns as $issn)
{
	$quoted_issns = '"' . join('","', $issn) . '"';

	// default filename
	$filename = $datadir . '/' . $issn[0] . '.jsonl';
	
	$sql = $sql = 'SELECT journal FROM ' . $table . ' WHERE issn IN (' . $quoted_issns . ') AND journal IS NOT NULL LIMIT 1';
	
	echo $sql . "\n";
	
	$data = do_query($sql);
	
	$filename = $datadir . '/' . $issn[0] . '-' . $data[0]->journal . '.jsonl';
	
	file_put_contents($filename, '');

	// get articles
	$sql = 'SELECT * FROM ' . $table . ' WHERE issn IN (' . $quoted_issns . ')';
	
	// any ISSN-specific things go here
	switch ($issn[0])
	{
		default:
			break;
	}	
	$sql .= ' ORDER BY CAST(volume AS INT), CAST(issue AS INT), CAST(spage AS INT)';


	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$csl = data_to_csl($obj);

		// Multiple languages?
		$sql = 'SELECT * FROM `multilingual` WHERE guid="' . $obj->guid . '"';
	
		$multilingual_data = do_query($sql);	
		foreach ($multilingual_data as $mdata)
		{	
			//print_r($multilingual_data);
	
			switch ($mdata->key)
			{
				case 'abstract':
				case 'title':
					if (!isset($csl->multi))
					{
						$csl->multi = new stdclass;
						$csl->multi->_key = new stdclass;					
					}
					if (!isset($csl->multi->_key->{$mdata->key}))
					{
						$csl->multi->_key->{$mdata->key} = new stdclass;					
					}
					$csl->multi->_key->{$mdata->key}->{$mdata->language} = $mdata->value;	
					
					if (!isset($mdata->language))
					{
						echo $obj->guid;
						print_r($mdata);
						exit();
					}
					break;
				
				default:
					break;
			}
		}
	
		// filename
		if ($filename == '')
		{
			$filename = $issn[0];

			$filename .= '.jsonl';
		}
		//print_r($csl);
		
		file_put_contents($filename, json_encode($csl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
	}

}



?>
