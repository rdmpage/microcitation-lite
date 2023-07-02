<?php

// Create a list of single DOIs for a combination of volume, issue, and series
// for T&F


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


$filename = 'tf.tsv';

$headings = array();

$row_count = 0;

$dois = array();

$file = @fopen($filename, "r") or die("couldn't open $filename");
	
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		"\t" 
		);
	
	$go = is_array($row);

	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
	
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$headings[$k]} = $v;
				}
			}
	
			//print_r($obj);	
			
			$sql = 'SELECT * FROM publications_doi 
				WHERE issn="0374-5481"
				AND volume="' . $obj->volume . '"
				AND issue="' . $obj->issue . '"
				AND year="' . $obj->year . '"
				LIMIT 1;';
				
			$data = do_query($sql);

			foreach ($data as $obj)
			{
				if (isset($obj->doi))
				{
					//echo $obj->doi . "\n";
					
					$dois[] = '"' . $obj->doi . '"';
				}
			}			
			
		}
	}	
	$row_count++;
}

echo join(",\n", $dois) . "\n";


?>
