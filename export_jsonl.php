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

// get CSL

$sql = 'SELECT * FROM publications WHERE guid="http://db.koreascholar.com/article?code=371999"';
$sql = 'SELECT * FROM publications WHERE journal="Insecta Koreana"';

$sql = 'SELECT * FROM publications_doi WHERE `publications_doi`.doi LIKE "10.5635/ASED%"';

$sql = 'SELECT * FROM publications where guid="10.5635/ASED.2018.34.1.050"';

$sql = 'SELECT * FROM publications_doi WHERE `publications_doi`.doi LIKE "10.5635/KJSZ%"';
$sql = 'SELECT * FROM publications WHERE `publications`.guid LIKE "http://koreascience.or.kr/article/%"';

$sql = 'SELECT * FROM publications WHERE `publications`.issn="1123-6787"';
$sql = 'SELECT * FROM publications WHERE `publications`.journal="Holarctic Lepidoptera"';
$sql = 'SELECT * FROM publications WHERE `publications`.issn="1008-0384"';

$sql = 'SELECT * FROM publications WHERE `publications`.issn="1028-6764"';
//$sql = 'SELECT * FROM publications WHERE guid = "https://www.zobodat.at/publikation_articles.php?id=489883"';

$sql = 'SELECT * FROM publications WHERE `publications`.issn="1021-5506" AND year < 2013 AND wikidata IS NULL';
$sql = 'SELECT * FROM publications WHERE `publications`.issn="1021-5506" AND year BETWEEN 1995 AND 2012 AND wikidata IS NULL';

// Solenodon
$sql = 'SELECT * FROM publications WHERE `publications`.issn="1608-0505"';


$sql = 'SELECT * FROM publications WHERE `publications`.issn="0001-3943" and wikidata is null';

$sql = 'SELECT * FROM publications WHERE issn="0084-5604" and year=2007 and wikidata is null and guid like "http%" ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

//$sql = 'SELECT * FROM publications WHERE guid="http://mail.izan.kiev.ua/vz-pdf/2007/1/01_Mitrofanov.pdf"';

$sql = 'SELECT * FROM publications WHERE issn="0136-006X" and wikidata is null ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';


$sql .= ' ORDER BY year, volume, issue, spage';

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
	
	// print_r($csl);
	
	echo json_encode($csl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}

?>
