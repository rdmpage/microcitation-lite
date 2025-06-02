<?php

// match two sets of references from two TSV files, we use "year" to "block" the
// data 

// Simplest TSV file is guid, title, year

/*
example SQL

### BioNames 

SELECT DISTINCT sici AS guid, title, IFNULL(volume,'') AS volume, IFNULL(year,'') AS year, IFNULL(spage,'') AS spage, IFNULL(epage,'') AS epage, IFNULL(doi,'') AS doi FROM names WHERE issn="2190-7307" ORDER BY year DESC;

guid	title	volume	year	spage	epage	doi


## Microcitation

SELECT guid, title, volume, year, spage, epage, doi FROM publications WHERE issn="XXXX-XXXX";



Bonn

SELECT DISTINCT sici AS guid, title, IFNULL(volume,'') AS volume, IFNULL(year,'') AS year, IFNULL(spage,'') AS spage, IFNULL(epage,'') AS epage, IFNULL(doi,'') AS doi FROM names WHERE issn="2190-7307" ORDER BY year DESC;

SELECT guid, title, volume, year, spage, epage, doi FROM publications_doi WHERE guid LIKE "10.20363/BZB%";

RSZ

SELECT DISTINCT sici AS guid, title, IFNULL(volume,'') AS volume, IFNULL(year,'') AS year, IFNULL(spage,'') AS spage, IFNULL(epage,'') AS epage, IFNULL(doi,'') AS doi 
FROM names WHERE issn="0035-418X" AND volume BETWEEN 122 AND 126;



SELECT guid, title, volume, year, spage, epage, doi FROM publications WHERE issn="0035-418X" AND guid LIKE "10.5281/zenodo.%";



*/

/*
multilingual sql

-- wanfang
SELECT guid, value AS title, year, issue, spage, epage FROM multilingual
INNER JOIN `publications` USING(guid)
WHERE multilingual.language = "zh" 
AND `publications`.issn IN ("0001-7302") AND url LIKE "https://wf.pub/perios/article:%"
AND year=1998 and key = 'title';


-- cnki
SELECT guid, key, value AS title, year, issue, spage, epage FROM multilingual
INNER JOIN `publications` USING(guid)
WHERE multilingual.language = "zh" 
AND `publications`.issn IN ("0001-7302") AND guid LIKE "https://oversea.cnki.net%"
AND year=1998 and key = 'title';

SELECT guid, key, value AS title, year, issue, spage, epage FROM multilingual
INNER JOIN `publications` USING(guid)
WHERE multilingual.language = "en" 
AND `publications`.issn IN ("1000-7083")
AND CAST(year) < 2015 and key = 'title';



*/


require_once(dirname(__FILE__) . '/compare.php');

//----------------------------------------------------------------------------------------
// get publications and group by year so we have "blocks"
function get_data($filename)
{
	$headings = array();

	$row_count = 0;

	$data = array();

	$file = @fopen($filename, "r") or die("couldn't open $filename");
		
	$file_handle = fopen($filename, "r");
	while (!feof($file_handle)) 
	{
		$line = trim(fgets($file_handle));
		
		$row = explode("\t",$line);
		
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
			
				if (isset($obj->year))
				{
					if (!isset($data[$obj->year]))
					{
						$data[$obj->year] = array();
					}
					$data[$obj->year][] = $obj;
				}
			}
		}	
		$row_count++;
	}

	return $data;
}

//----------------------------------------------------------------------------------------

//get data and group by years to minimise comparisons we need to make


$one = get_data('bionames.tsv'); // references from BioNames
$two = get_data('micro.tsv'); // references from microcitation

//print_r($one);
//print_r($two);

//exit();

// compare

$verbose = false;
//$verbose = true;

$missing_one = array();
$missing_two = array();

foreach ($one as $year => $articles)
{
	if (isset($one[$year]) && isset($two[$year]))
	{
		if ($verbose)
		{
			echo "\n\n-- $year --\n";
		}
		
		$k1 = array();
		$k2 = array();
		

		foreach ($one[$year] as $o1)
		{
			//echo $o1->title . "\n";
			$k1[] = $o1;
		}

		//echo "\n\n";

		foreach ($two[$year] as $o2)
		{
			// echo $o2->title . "\n";
			
			// hacks
			$o2->title = str_replace('書評 ', '', $o2->title);
			//echo $o2->title . "\n";
			
			$k2[] = $o2;
		}

		$m = count($k1);
		$n = count($k2);
		
		$k1_list = range(0, $m-1);
		$k2_list = range(0, $n-1);
		
		
		//print_r($k1);
		//print_r($k2);
		
		$best_matches = array();

		for ($i = 0; $i < $m; $i++)
		{
			$best_hit = -1;
			$best_normalised = array(0,0);
					
			for ($j = 0; $j < $n; $j++)
			{								
				// extra cleaning?
				$text1 = $k1[$i]->title;
				$text2 = $k2[$j]->title;
				
				
				if (preg_match('/^(.*) \/ (.*)$/', $text1, $matches))
				{
					$text1 = $matches[1];
				}
				
				
				//echo "$text1\n";
				//echo "$text2\n";
				
				$result = compare_common_subsequence($text1, $text2);
				
				
				if ($result->normalised[1] > 0.80)
				{
					// one string is almost an exact substring of the other
					if ($result->normalised[0] > 0.80)
					{
						if ($result->normalised[1] > $best_normalised[1] && $result->normalised[0] >= $best_normalised[0])
						{
							$best_hit = $j;
							$best_normalised = $result->normalised;
						}
					}
				}
			}
				
			if ($best_hit != -1)
			{
				$j = $best_hit;
				
				if ($verbose)
				{
					echo "\n-- " . $k1[$i]->title . "\n";
					echo "-- " . $k2[$j]->title . "\n";
				}
		
				//------------------------------------------------------------------------
				// do something here, this may need to be edited for the specific task
				
				if (1)
				{
					// BioNames
					if (isset($k2[$j]->doi))
					{
						echo 'UPDATE names SET doi="' . $k2[$j]->doi . '" WHERE sici="' . $k1[$i]->guid . '";' . "\n";
					}	
					
					if (isset($k2[$j]->url))
					{
						echo 'UPDATE names SET url="' . $k2[$j]->url . '" WHERE sici="' . $k1[$i]->guid . '";' . "\n";
					}				

					if (isset($k2[$j]->pdf))
					{
						echo 'UPDATE names SET pdf="' . $k2[$j]->pdf . '" WHERE sici="' . $k1[$i]->guid . '";' . "\n";
					}				
								
				}
			}
		}
		
		//print_r($k1_list);
		//print_r($k2_list);

		foreach ($k1_list as $i)
		{
			$missing_one[] = $k1[$i]->guid;
		}
		
		foreach ($k2_list as $j)
		{
			$missing_two[] = $k2[$j]->guid;
		}
	}
}


//print_r($missing_one);
//print_r($missing_two);


?>
