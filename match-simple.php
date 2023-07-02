<?php

// match two sets of references from two TSV files, we use "year" to "block" the
// data 

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

$one = get_data('one.tsv');
$two = get_data('two.tsv');

//print_r($one);
//print_r($two);

// compare

$verbose = false;
$verbose = true;

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
			//echo $o2->title . "\n";
			$k2[] = $o2;
		}

		$m = count($k1);
		$n = count($k2);
		
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
				
				$result = compare_common_subsequence($text1, $text2);
				
				if ($result->normalised[1] > 0.95)
				{
					// one string is almost an exact substring of the other
					if ($result->normalised[0] > 0.90)
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
				
				if (isset($k2[$j]->wikidata))
				{
					echo 'UPDATE publications SET wikidata="' . $k2[$j]->wikidata . '" WHERE guid="' . $k1[$i]->guid . '";' . "\n";
				}
				
				// one has wikidata, update two
				if (isset($k1[$i]->wikidata))
				{
					echo 'UPDATE publications SET wikidata="' . $k1[$i]->wikidata . '" WHERE guid="' . $k2[$j]->guid . '";' . "\n";
				}
				
				// two has PDF, update one
				if (isset($k2[$j]->pdf))
				{
					echo 'UPDATE publications_doi SET pdf="' . $k2[$j]->pdf . '" WHERE guid="' . $k1[$i]->guid . '";' . "\n";
				}
				
				// two has internetarchive, update one
				if (isset($k2[$j]->internetarchive))
				{
					echo 'UPDATE publications_doi SET internetarchive="' . $k2[$j]->internetarchive . '" WHERE guid="' . $k1[$i]->guid . '";' . "\n";
				}
				
				// two has internetarchive, update one
				if (isset($k2[$j]->work))
				{
					echo 'UPDATE publications SET wikidata="' . str_replace('http://www.wikidata.org/entity/', '', $k2[$j]->work) . '" WHERE guid="' . $k1[$i]->guid . '";' . "\n";
				}

				// two has researchgate
				if (isset($k2[$j]->id) && isset($k1[$i]->wikidata))
				{
					echo $k1[$i]->wikidata . "\tP5875\t\"" . $k2[$j]->id . "\"\n";
				}
				
				//------------------------------------------------------------------------
				//print_r($k1[$i]);
				//print_r($k2[$j]);
				
				$ok = true;
				
				// sanity checks
				if ($ok && isset($k1[$i]->volume) && isset($k2[$j]->volume))
				{					
					$ok = ($k1[$i]->volume == $k2[$j]->volume);
				}
				
				if ($ok && isset($k1[$i]->spage) && isset($k2[$j]->spage))
				{
					$spage1 = $k1[$i]->spage;
					$spage2 = $k2[$j]->spage;
					
					$spage1 = preg_replace('/^0+/', '', $spage1);
					$spage2 = preg_replace('/^0+/', '', $spage2);
					
					$ok = ($spage1 == $spage2);
				}
				
				if ($ok)
				{
					// do stuff
					echo 'UPDATE publications SET alternative_id="' . $k2[$j]->doi . '" WHERE guid="' . $k1[$i]->guid . '";' . "\n";
				}
				else
				{
					echo "-- *** false match ***\n";
				}
			
			}
		}
	}
}

?>
