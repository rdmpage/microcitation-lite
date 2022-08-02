<?php

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

// get CSL

$sql = 'SELECT * FROM publications WHERE guid="http://db.koreascholar.com/article?code=371999"';
$sql = 'SELECT * FROM publications WHERE journal="Insecta Koreana"';

$data = do_query($sql);

foreach ($data as $obj)
{
	$csl = json_decode($obj->json);

	// print_r($csl);

	// enhance 

	// PDF?
	if (isset($obj->pdf))
	{
		$add = true;
		if (!isset($csl->link))
		{
			$csl->link = array();	
		}
		else
		{
			foreach ($csl->link as $link)
			{
				if ($link->URL == $obj->pdf)
				{
					$add = false;
				}
			}
		}	
		if ($add)
		{
			$link = new stdclass;
			$link->URL = $obj->pdf;
			$link->{'content-type'} = "application/pdf";
			
			$csl->link[] = $link;
		}
	}

	// Multiple languages?
	$sql = 'SELECT * FROM `multilingual` WHERE guid="' . $obj->guid . '"';
	
	$multilingual_data = do_query($sql);	
	foreach ($multilingual_data as $mdata)
	{
		switch ($mdata->key)
		{
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
	
	// print_r($csl);
	
	echo json_encode($csl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}

?>
