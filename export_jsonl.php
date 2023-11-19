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

$sql = 'SELECT * FROM publications WHERE issn="0188-4018" AND wikidata IS NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications_doi WHERE guid="10.16373/j.cnki.ahr.200084"';
$sql = 'SELECT * FROM publications_doi WHERE guid="10.3969/j.issn.1005-9628.2018.01.07"';

$sql = 'SELECT * FROM publications_doi WHERE issn="1005-9628" AND wikidata IS NULL';

$sql = 'SELECT * FROM publications WHERE issn="2079-0139" and wikidata IS NULL';

$sql = 'SELECT * FROM publications WHERE issn="1864-8312" AND volume BETWEEN 77 AND 78 AND wikidata IS NULL';

$sql = 'select * from publications where issn="1864-8312" and volume <77 and wikidata is null';

$sql = 'select * from publications where issn="2095-0357" and cast(volume as int) < 6 and wikidata is null';
$sql = 'select * from publications_doi where issn="2095-0357" and wikidata is null';

$sql = 'select * from publications where issn="1814-6090" and wikidata is null';

$sql = "select * from publications where issn='0013-8738' and wikidata is null";

$sql = "select * from publications where issn='0367-1445' and wikidata is null and year < 2019";

$sql = "select  * from publications where issn='1005-9628' and wikidata is null";
$sql = "select  * from publications where issn='1000-7482' and wikidata is null and year >= 2020";

$sql ="select * from publications where guid='http://med.wanfangdata.com.cn/Paper/Detail/PeriodicalPaper_zxxb200401004'";

$sql = "select  * from publications where issn='0065-1710' and wikidata is null and year <= 1990";

$sql = "select * from publications where issn='0065-1710' and guid LIKE '%azc_v%' and wikidata is null";

$sql = "select * from publications where issn='0342-412X' and wikidata is null";
$sql = "select * from publications where issn='1371-7057' and wikidata is null";

$sql = "select * from publications_doi where guid='10.19615/j.cnki.1000-3118.200618'";

$sql = 'select * from publications_doi where guid in (
    "10.6119/jmst-013-1219-12",
    "10.6662/tesfe.202111_41(4).002",
    "10.6542/efntu.201912_33(4).0005",
    "10.6620/zs.2022.61-02",
    "10.6542/efntu.2017.31(1).3"
    )';

$sql = 'select * from publications where issn="0077-2135" and wikidata IS NULL';

$sql = 'SELECT * FROM publications WHERE issn="0867-1710" and wikidata IS NULL 
and spage IS NOT NULL and authors IS NOT NULL';

$sql = 'select * from publications where issn="0366-3353" and wikidata IS NULL';

$sql = 'select * from publications where issn="0366-3353" and wikidata IS NULL';

$sql = "select * from publications where guid='https://dialnet.unirioja.es/servlet/articulo?codigo=3886942'";


$sql = "select * from publications where issn='1810-9586' and wikidata is null";
$sql = "select * from publications where issn='0170-110X' and authors <> 'Redaktion' and title <> 'Buchsprechungen' and epage IS NULL";

$sql = "select * from publications where issn='1661-8041' and wikidata is null";
$sql = "select * from publications where issn='0370-3207' and wikidata is null";

$sql = "select * from publications where issn='1011-5498' and spage != 0";

$sql = "select * from publications_doi where doi in ('10.4467/16890027AP.12.023.0783')";

$sql = "select * from publications where issn='2195-9889'";

$sql = "select * from publications where issn='1013-2732'";

$sql = "select * from publications where doi in ('10.6043/j.issn.0438-0479.202008004')";

$sql = "SELECT * FROM publications WHERE issn='0013-8827' AND flag=1";

$sql = "SELECT * FROM publications WHERE issn='1004-5260'";

$sql = "SELECT * FROM publications WHERE issn in ('0747-8194','0096-7815')";

$sql = "SELECT * FROM publications WHERE issn='0385-5643'";

$sql = "SELECT * FROM publications WHERE issn='0084-800X' and volume is not null";

$sql = "SELECT * FROM publications WHERE issn='0373-2975' AND volume != 68 and wikidata IS NULL";

$sql = "SELECT * FROM publications WHERE issn='2665-0347' and flag=1";

$sql = "SELECT * FROM publications WHERE guid='https://produccioncientificaluz.org/index.php/anartia/article/view/40512'";

$sql = "select * from publications WHERE issn='2307-5031' and flag=1";

$sql = "select * from publications where issn='0013-886X' AND year BETWEEN 1950 AND 1960 AND pdf IS NOT NULL AND wikidata is null";

$sql = "SELECT * FROM publications WHERE issn='0155-4131' AND wikidata IS NULL";
$sql = "SELECT * FROM publications WHERE issn='0069-2379' AND wikidata IS NULL";

$sql = "SELECT * FROM publications WHERE journal='Münchner Koleopterologische Zeitschrift' AND volume=2 and authors IS NOT NULL";


$sql = "SELECT * FROM publications WHERE issn='0453-1906' AND wikidata IS NULL";
$sql = "SELECT * FROM publications_doi WHERE issn='0453-1906' AND wikidata IS NULL and authors IS NOT NULL";

$sql = "SELECT * FROM publications WHERE issn='0253-116X' AND wikidata IS NULL and guid like '%www.zobodat.at%'";

$sql = "SELECT * FROM publications WHERE issn='0554-2111' AND authors != '- -'";

$sql = "SELECT * FROM publications WHERE issn='2581-8686' and year > 1962";

$sql = "SELECT * FROM publications WHERE issn='0753-4973'";

$sql = "SELECT * FROM publications WHERE issn='0368-8720'";

$sql .= ' ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED)';

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
	
	//print_r($csl);
	
	echo json_encode($csl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}

?>
