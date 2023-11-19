<?php

// Parse a Wikidata query result

/*

# basic query
SELECT * WHERE {
  ?work wdt:P1433 wd:Q2005798;
    wdt:P1476 ?title.
  OPTIONAL { ?work wdt:P356 ?doi. }
  OPTIONAL { ?work wdt:P698 ?pmid. }
  OPTIONAL { ?work wdt:P1184 ?handle. }
  OPTIONAL { ?work wdt:P5315 ?biostor. }
  OPTIONAL { ?work wdt:P888 ?jstor. }
  OPTIONAL { ?work wdt:P478 ?volume. }
  OPTIONAL { ?work wdt:P433 ?issues. }
  OPTIONAL { ?work wdt:P304 ?pages. }
  OPTIONAL { ?work wdt:P577 ?date . }
  BIND (YEAR(?date) AS ?year)
}

*/


$row_count = 0;

$header = array();
$header_lookup = array();

$filename = 'query.tsv';

//$filename = '/Users/rpage/Downloads/query-3.tsv';
$filename = '/Users/rpage/Downloads/query.tsv';
//$filename = '/Users/rpage/Downloads/query-2.tsv';


$file = @fopen($filename, "r") or die("couldn't open $filename");		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = explode("\t", trim(fgets($file_handle)));
		
	$go = is_array($row);
			
	if ($go && ($row_count == 0))
	{
		$header = $row;
		
		$n = count($header);
		for ($i = 0; $i < $n; $i++)
		{
			$header_lookup[$header[$i]] = $i;
		}
		
		$go = false;
	}
	if ($go)
	{
		//print_r($row);
		
		if (count($row) != 0)
		{		
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$header[$k]} = trim($v);
				}
			}

			//print_r($obj);
			
			// do something here
			
			// add Wikidata based on DOI
			if (1)
			{				
				if (isset($obj->doi))
				{
			
					$sql = 'UPDATE publications_doi SET wikidata="' 
						. preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $obj->work) . '"'
						. ' WHERE doi="' . strtolower($obj->doi) . '"'
						. ' AND wikidata IS NULL'
						. ';';
					
					echo $sql . "\n";
			
				}
			}
			
			// add Wikidata based on CNKI
			if (0)
			{				
				if (isset($obj->cnki))
				{
			
					$sql = 'UPDATE publications SET wikidata="' 
						. preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $obj->work) . '"'
						. ' WHERE cnki="' . $obj->cnki . '"'
						. ' AND wikidata IS NULL'
						. ';';
					
					echo $sql . "\n";
			
				}
			}
			
			
			if (0)
			{
				// Add Wikidata based on metadata match
				if (
					isset($obj->volume)
					 && isset($obj->pages)
				 )
				{
					$issn = '0085-4417';
					$issn = '0022-2062';
									
					$parts = preg_split('/[-|–]/u', $obj->pages);
					
					$sql = 'UPDATE publications_doi SET wikidata="' 
						. preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $obj->work) . '"'
						. ' WHERE issn="' . $issn . '"'
						. ' AND volume="' .$obj->volume . '"'
						. ' AND spage="' . $parts[0] . '"';
						
						if (count($parts) == 2)
						{
							$sql .= ' AND epage="' . $parts[1] . '"';
						}
						else
						{
							$sql .= ' AND epage="' . $parts[0] . '"';						
						}
						
						$sql .= ' AND title="' . str_replace("'", "''", $obj->title) . '"';
						
						$sql .=  ' AND wikidata IS NULL'
						. ';';					
					echo $sql . "\n";
				}
			}		
			
			if (0)
			{
				// Add records from Wikidata based on metadata match
				
				if (isset($obj->work))
				{
				
					$table = 'publications';
				
					$issn 		= '0375-099X';
					$journal 	= 'Records of the Indian Museum';

					//$issn 		= '0375-1511';
					//$journal 	= 'Records of the Zoological Survey of India';

				
					$keys = array();
					$values = array();
				
					$keys[] = 'guid';
					$values[] = '"' . preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $obj->work) . '"';

					$keys[] = 'journal';
					$values[] = '"' . $journal . '"';
					
					$keys[] = 'issn';
					$values[] = '"' . $issn . '"';

					foreach ($obj as $k => $v)
					{
						switch ($k)
						{		
							case 'title':
							case 'volume':
							case 'issue':
							case 'year':
							case 'doi':
								$keys[] = $k;
								$values[] = '"' . str_replace('"', '""', $v) . '"';	
								break;	
							
							case 'issues':	
								$keys[] = 'issue';
								$values[] = '"' . str_replace('"', '""', $v) . '"';	
								break;	
							
							case 'pages':
								$parts = preg_split('/[-|–]/u', $obj->pages);

								$keys[] = 'spage';
								$values[] = '"' . str_replace('"', '""', $parts[0]) . '"';	
						
								if (count($parts) == 2)
								{
									$keys[] = 'epage';
									$values[] = '"' . str_replace('"', '""', $parts[1]) . '"';	
								}
								break;
								
							case 'url':
								if (preg_match('/\.pdf$/', $v))
								{
									$keys[] = 'pdf';
									$values[] = '"' . str_replace('"', '""', $v) . '"';									
								}
								break;
							
							default:
								break;
							}
						}
					
					if (1)
					{	
						$sql = 'REPLACE INTO ' . $table . '(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";
					}
					else
					{
						$sql = 'INSERT OR IGNORE INTO ' . $table . '(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";
					}
				
					echo $sql . "\n";
				}
			}			
				



		}		
		
	}

	$row_count++;
	
	// if ($row_count > 3) exit();
}

?>
