<?php

// Fix encoding errors

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
// from ChatGPT
function unicodeToAscii($text) {
    $result = '';

    foreach (mb_str_split($text) as $char) {
        $code = mb_ord($char, 'UTF-8');
        
        echo $char . ' ' . $code . ' ' . dechex($code) . "\n";
        
        // Check for Mathematical Italic Capital (𝑨-𝒁)
        if ($code >= 0x1D434 && $code <= 0x1D44D) {
            $result .= chr($code - 0x1D434 + ord('A'));
        }
        // Check for Mathematical Italic Small (𝒶-𝓏)
        elseif ($code >= 0x1D44E && $code <= 0x1D467) {
            $result .= chr($code - 0x1D44E + ord('a'));
        }
        // Check for Sans-Serif Italic Capital (𝘈-𝘡)
        elseif ($code >= 0x1D608 && $code <= 0x1D621) {
            $result .= chr($code - 0x1D608 + ord('A'));
        }
        // Check for Sans-Serif Italic Small (𝘢-𝘻)
        elseif ($code >= 0x1D622 && $code <= 0x1D63B) {
            $result .= chr($code - 0x1D622 + ord('a'));
        }
        // Non-italic characters remain unchanged
        else {
            $result .= $char;
        }
    }

    return $result;
}

//----------------------------------------------------------------------------------------


if (0)
{
	$sql = 'SELECT * FROM publications where issn="0453-1906" AND pdf IS NOT NULL';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$pdf = $obj->pdf;
	
		echo "-- " . $obj->pdf . "\n";
	
		// http://nh.kanagawa-museum.jp/files/data/pdf/bulletin/43/bull43_33-62_tanaka_n.pdf
		if (preg_match('/bull\d+_0?(?<spage>\d+)[-|_]0?(?<epage>\d+)_/', $obj->pdf, $m))
		{
		
			echo 'UPDATE publications SET spage="' . $m['spage'] . '", epage="' . $m['epage'] . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
		else
		{
			echo "-- *** No match ***\n";
		
		}
	}
}


if (0)
{
	$sql = 'SELECT * FROM publications where issn="2318-2407" AND title LIKE "%Ã%"';
	
	$sql = 'SELECT * FROM publications where guid="10.37856/bja.v57i4.4118"';
	
	$sql = 'SELECT * FROM publications where issn="2318-2407" AND title LIKE "%©%"';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$title = $obj->title;
		echo "-- " . $title . "\n";
		
		$title = mb_convert_encoding($title, 'WINDOWS-1252', 'UTF-8');	
		echo "-- " . $title . "\n";
	
		echo "UPDATE publications SET title='" . str_replace("'", "''", $title) . "' WHERE guid='" . $obj->guid . "';\n";
	}
}

if (0)
{
	// Works(ish)
	$sql = 'SELECT * FROM publications where issn="2318-2407"';
	
	$sql = 'SELECT * FROM publications where guid="10.37856/bja.v42i4.1924"';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$title = $obj->title;
		echo "-- " . $title . "\n";
		
		$title = mb_convert_encoding($title, 'WINDOWS-1252', 'UTF-8');
	
		echo "-- " . $title . "\n";
	
		echo "UPDATE publications SET title='" . str_replace("'", "''", $title) . "' WHERE guid='" . $obj->guid . "';\n";
	}
}

if (1)
{
	// Doesn't work for some authors
	$sql = 'SELECT * FROM publications where issn="2318-2407" AND authors LIKE "%Ã%"';
	
	//$sql = 'SELECT * FROM publications where guid="10.37856/bja.v1i4.3364"';

	$data = do_query($sql);

	foreach ($data as $obj)
	{
		$guid = $obj->guid;	
		$authors = $obj->authors;
		echo "-- " . $authors . "\n";
		
		//$authors = mb_convert_encoding($authors, 'UTF-8', 'ISO-8859-1');
		
		
		$authors = str_replace('ã§Ãµ', 'çõ', $authors);
		$authors = str_replace('ã£O', 'ão', $authors);
		$authors = str_replace('ã©', 'é', $authors);
		$authors = str_replace('ã¢N', 'ân', $authors);
		$authors = str_replace('ã¨', 'è', $authors);
		
		//$authors = iconv("WINDOWS-1252", "UTF-8", $authors);
		
		//$text = unicodeToAscii($authors);
		
	
		
		
		if ($authors != $obj->authors)
		{
			echo "-- " . $authors . "\n";
			echo 'UPDATE publications SET authors="' . str_replace("'", "''", $authors) . '" WHERE guid="' . $obj->guid . '";' . "\n";
		}
	}
}


?>
