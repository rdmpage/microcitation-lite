<?php

// return one record

$guid = '';

if (isset($_GET['id']))
{
	$guid = $_GET['id'];
}

$pdo = new PDO('sqlite:../microcitation.db');

require_once (dirname(dirname(__FILE__)) . '/db_to_csl.php');

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

$results = array();

$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';

$data = do_query($sql);

foreach ($data as $obj)
{
	$csl = data_to_csl($obj);

	// Multiple languages?
	$sql = 'SELECT * FROM `multilingual` WHERE guid="' . $obj->guid . '"';
	
	$multilingual_data = do_query($sql);	
	foreach ($multilingual_data as $mdata)
	{	
		// print_r($multilingual_data);
	
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
				break;
				
			default:
				break;
		}
	}
	
	$results[] = $csl;
	

}

echo json_encode($results, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";

?>
