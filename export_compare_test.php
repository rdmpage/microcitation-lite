<?php

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
// Convert result from SQLite to CSL
function row_to_csl($obj)
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
	
	return $csl;
}

//----------------------------------------------------------------------------------------

// Case 1: pairs of GUIDs for specific examples we want to explore

if (1)
{
	// same article but no multilingual data :(
	$guids = array(
	'https://wf.pub/perios/article:zzyjhk200104001',
	'https://oversea.cnki.net/kcms/detail/detail.aspx?dbcode=CJFD&filename=ZZYJ200104000',
	);
	
	// same article but multilingual data :)
	$guids = array(
	'10.3969/j.issn.1674-5507.2007.03.011',
	'https://oversea.cnki.net/kcms/detail/detail.aspx?dbcode=CJFD&filename=BEAR200703010',
	);
	
	$guids = array(
	'http://qdhys.ijournal.cn/hykxjk/ch/reader/view_abstract.aspx?file_no=20024416&flag=1',
	'http://qdhys.ijournal.cn/hykxjk/ch/reader/view_abstract.aspx?file_no=20024416&flag=1'
	);
	
	$result = array();
	
	foreach ($guids as $guid)
	{
		$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';
		$data = do_query($sql);
	
		foreach ($data as $obj)
		{
			$result[] = row_to_csl($obj);
		}
	
	}
	
	echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}

// Case 1a: pairs of GUIDs for specific examples we want to explore, one from publications_doi, one not

if (0)
{
	
	$guids = array(
	'10.1080/21686351.1977.12278666',
	'https://gallica.bnf.fr/ark:/12148/bpt6k6124854j/f262',
	);
	
	$guids = array(
	'10.3989/ajbm.2417',
	'https://dialnet.unirioja.es/servlet/articulo?codigo=5817646',
	);

	$result = array();
	
	foreach ($guids as $guid)
	{
		if (preg_match('/^10\./', $guid))
		{
			$sql = 'SELECT * FROM publications_doi WHERE guid="' . $guid . '"';		
		}
		else
		{
			$sql = 'SELECT * FROM publications WHERE guid="' . $guid . '"';
		}
		$data = do_query($sql);
	
		foreach ($data as $obj)
		{
			$result[] = row_to_csl($obj);
		}
	
	}
	
	echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";


}



// Case 2: random pairs of citations


// Case 3: pairs that have something in common but are different

if (0)
{
	$issn = '0867-1710';
	$year = '1991';
	$author = 'J. A. Lis';
	
	$issn = '0374-5481';
	$year = '1910';
	$author = 'W.L. Distant';
	
	$table = 'publications_doi';	
	
	$sql = 'SELECT * FROM ' . $table . ' WHERE issn="' . $issn . '" AND year="' . $year . '" AND authors="' . $author . '" ORDER BY title';
	
	echo $sql . "\n";
	
	$data = do_query($sql);
	
	print_r($data);
}

	
// Case 4: pairs that have different sources but are the same




?>
