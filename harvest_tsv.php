<?php

require_once (dirname(__FILE__) . '/nameparse.php');
require_once (dirname(__FILE__) . '/csl_utils.php');



//----------------------------------------------------------------------------------------

$filename = 'test.tsv';

$headings = array();

$row_count = 0;

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
		
			// print_r($obj);	
			
			// convert to CSL
			
			$reference = new stdclass;

			foreach ($obj as $k => $v)
			{
				switch ($k)
				{
					case 'title':
						$reference->title = $v;
						break;
		
					case 'volume':
						$reference->{$k} = $v;
						break;

					case 'issue':
						$reference->issue = $v;
						break;
						
					case 'spage':
					case 'epage':
						echo "*** TO DO ***\n";
						exit();
						break;
			
					case 'journal':
						$reference->{'container-title'} = $v;
						break;
							
					case 'issn':
						if (preg_match('/([0-9]{4}-[0-9]{3}([0-9]|X))/', $v, $m))
						{
							$reference->ISSN[] = $m[1];
						}
						break;

					case 'isbn':
						$reference->ISBN[] = $v;
						break;
			
					case 'url':
						$reference->URL = $v;
						break;

					case 'doi':
						$reference->DOI = $v;
						$reference->DOI = preg_replace('/https?:\/\/(dx\.)?doi\.org\//', '' , $reference->DOI);
						break;
			
					case 'type':
						switch ($v)
						{
							case 'article':
								$reference->type = 'article-journal';
								break;
					
							default:
								$reference->type = $v;
								break;	
						}
						break;
			
					case 'year':
						$reference->issued = new stdclass;
						$reference->issued->{'date-parts'} = array();
						$reference->issued->{'date-parts'}[0] = array((Integer)$v);
						break;

					case 'publisher':
						$reference->$k = $v;
						break;
			
					case 'authors':
						$names = explode(';', $v);
			
						foreach ($names as $name)
						{				
							$author = new stdclass;
			
							// Get parts of name
							$parts = parse_name($name);
			
							if (isset($parts['last']))
							{
								$author->family = $parts['last'];
							}
			
							if (isset($parts['suffix']))
							{
								$author->suffix = $parts['suffix'];
							}
			
							if (isset($parts['first']))
							{
								$author->given = $parts['first'];
				
								if (array_key_exists('middle', $parts))
								{
									$author->given .= ' ' . $parts['middle'];
								}
							}
				
							if (!isset($author->family) && isset($author->given))
							{
								$author->literal = $author->given;
								unset($author->given);
							}
			
							$reference->author[] = $author;
						}
						break;
						
					case 'pdf':
						$link = new stdclass;
						$link->{'content-type'} = 'application/pdf';			
						$link->URL = $v;
						$reference->link[] = $link;
						break;
					
							
					default:
						break;
		
				}
			}
			
			//print_r($reference);
			
	
			echo csl_to_sql($reference);
			
			
		}
	}	
	$row_count++;
}

?>
