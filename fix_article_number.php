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

$sql = 'SELECT * FROM publications where issn="1083-446X"';

$data = do_query($sql);

$articles = array();

foreach ($data as $obj)
{
	$article_number = $obj->article_number;
	$article_number = preg_replace('/^e/', '', $article_number);
	$article_number = preg_replace('/[a-z]+$/', '', $article_number);
	
	$articles[] = $article_number;
	
	
}

sort($articles, SORT_NUMERIC);

print_r($articles);

$missing = array();

for ($i = 0; $i <= 326; $i++)
{
	if (!in_array($i, $articles))
	{
		$missing[] = $i;
	}
}

print_r($missing);



?>
