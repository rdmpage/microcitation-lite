<?php

// Export to RIS

require_once (dirname(__FILE__) . '/csl_utils.php');
require_once (dirname(__FILE__) . '/db_to_csl.php');

$pdo = new PDO('sqlite:' . dirname(__FILE__) . '/microcitation.db');

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
$sql = 'SELECT * FROM publications WHERE `publications`.doi LIKE "10.5635/ASED%"';

$sql = 'SELECT * FROM publications WHERE `publications`.doi LIKE "10.5635/KJSZ%"';

$sql = 'SELECT * FROM publications WHERE `publications`.guid LIKE "http://koreascience.or.kr/article/%"';

$sql = 'SELECT * FROM publications_doi WHERE issn="1945-9475" AND authors IS NOT NULL AND volume="7" ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="1123-6787" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE `publications`.journal="Holarctic Lepidoptera" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="1028-6764" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="1608-0505" AND pdf IS NOT NULL and volume=8 ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="0001-3943" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'select * from ia_tmp 
inner join publications ON ia_tmp.ia = publications.internetarchive
where issn="0084-5604"
AND ia_tmp.title <> publications.title;';

$sql = 'select * from publications where issn="0084-5604" and internetarchive is null and pdf is not null and title is not null ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'select * from publications where pdf IN (
"http://mail.izan.kiev.ua/vz-pdf/2009/4/08_Protasov&all.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2009/5/12_Dyadichko&Gramma.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2010/2/08_Matushkina%20&%20Bach.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2010/3/11_Gural-Sverlova&al.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2010/4/09_Shevchuk&Dovgal.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2010/6/03_Kornyushin&Gereben.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2011/2/06_Glotov&all.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2011/2/09_Manjary&Roy.pdf",
"http://mail.izan.kiev.ua/vz-pdf/2011/6/01_Kornyushin&all.pdf"
)';

$sql = 'SELECT * FROM publications WHERE issn="0136-006X" 
AND volume BETWEEN 1 AND 18
AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="0136-006X" 
AND (volume BETWEEN 19 AND 31) OR (volume LIKE "S%")
AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE pdf="https://kmkjournals.com/upload/PDF/ArthropodaSelecta/30/30_1_001_Spiridonov_Obit_short_for_Inet.pdf"';

// JSTOR to BioStor
$sql = 'SELECT * FROM publications WHERE issn="0096-3844" AND authors IS NOT NULL and spage IS NOT NULL 
AND year < 1910
ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="0188-4018" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="1000-7482" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

//$sql = 'SELECT * FROM publications where issn="0136-006X" and issue LIKE "%–%" AND pdf IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';


$sql = 'SELECT * FROM publications WHERE issn="0065-1710" AND pdf IS NOT NULL and internetarchive IS NULL and year="2018" ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE journal="Zoölogische Monographieën"';

$sql = 'SELECT * FROM publications WHERE issn="0867-1710" and internetarchive IS NULL and spage IS NOT NULL and authors IS NOT NULL ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED);';

$sql = 'SELECT * FROM publications WHERE issn="0032-3780"';

$sql = 'SELECT * FROM publications_doi WHERE issn="0093-4666" AND pdf IS NOT NULL AND license="http://creativecommons.org/licenses/by-nc-nd/4.0/"';

$sql = 'SELECT * FROM publications WHERE issn="0031-5850" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications_doi WHERE issn="2077-7019" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications_doi WHERE pdf IN ("http://www.mycosphere.org/pdfs/MC2_5_No6.pdf", "")';

$sql = "SELECT * FROM publications WHERE issn='0071-1268' and pdf is not null and internetarchive is null";

$sql = "SELECT * FROM publications WHERE issn='2665-0347' and pdf is not null and internetarchive is null";

$sql = "select * from publications where internetarchive in ('anartia-33660-35463','anartia-37320-40718','anartia-37311-40702','anartia-37310-40700','anartia-37312-40704','anartia-37313-40706','anartia-37314-40708','anartia-37315-40710','anartia-37316-40712','anartia-37319-40716','anartia-40509-46102','anartia-40510-46103','anartia-40511-46105','anartia-40513-46109','anartia-40514-46111','anartia-40515-46113','anartia-40516-46115')";

$sql = 'select * from publications where internetarchive in ("anartia-40512-46107","anartia-37308-40698")';

$sql = "select * from publications_doi WHERE issn='0069-2379' and pdf is not null";
$sql = "select * from publications WHERE issn='0069-2379' and year < 2007 and pdf is not null";

// Austrobaileya
$sql = "select * from publications WHERE issn='0155-4131' and volume=11";

// Revista Pittieria
$sql = "select * from publications WHERE issn='0554-2111' and pdf IS NOT NULL";

$sql = "SELECT * FROM publications WHERE issn='2581-8686' and year > 1962 and pdf IS NOT NULL";

$sql = "SELECT * FROM publications WHERE issn='0753-4973' AND volume IN (3,4)";

// Breviora DOI
$sql = "SELECT guid, type, title, journal, authors, issn, volume, spage, epage, doi, year, date FROM publications_doi WHERE issn='0006-9698'";

// Breviora no DOI
$sql = "SELECT * from publications  WHERE issn='0006-9698' and CAST(volume as INTEGER) < 512 and biostor is null order by CAST(volume as INTEGER);";

// 2304-7534
$sql = "SELECT * from publications  WHERE issn='2304-7534' AND authors IS NOT NULL AND year BETWEEN 1870 AND 1880";

// 0368-8720
$sql = "SELECT * from publications  WHERE issn='0368-8720' AND pdf LIKE 'https://anales.ib.unam.mx/%'";

// 0368-2935 Journal of the Linnean Society of London, Zoology
$sql = "SELECT * from publications_doi  WHERE issn='0368-2935' AND volume BETWEEN 11 AND 22 AND spage NOT LIKE 'v%'";

$sql = "SELECT * from publications_doi  WHERE issn='1945-9475' AND volume=8 AND spage NOT LIKE 'v%'";

// BZN
$sql = "SELECT * from publications_doi  WHERE issn='0007-5167' AND type='journal-article' AND volume IN (73)";

// Ibis
$sql = "SELECT * from publications_doi  WHERE issn='0019-1019' AND type='journal-article' AND volume = 45 AND spage IS NOT NULL";
//$sql = "SELECT * FROM publications_doi WHERE guid='10.1111/j.1474-919x.1922.tb01300.x'";
//$sql = "SELECT * FROM publications_doi WHERE guid='10.1111/j.1474-919x.1922.tb01301.x'";

// Mycotaxon

$sql = "SELECT * from publications_doi  WHERE issn='0093-4666' AND type='journal-article' AND volume IN (115)";


$sql .= ' ORDER BY CAST(volume as SIGNED), CAST(spage AS SIGNED)';


if (0)
{
	// Journal of the Asiatic Society of Bengal

	$sql = "select * from publications 
	WHERE issn='0368-1068' 
	AND printf('%d', spage) = spage
	AND year='1908'";

	$sql = "select * from publications where issn='0368-1068' and authors like '%Moore%'";
	//$sql = "select * from publications where issn='0368-1068' and title like '%blat%'";
	
	$sql = "select * from publications 
	WHERE issn='0368-1068' 
	AND printf('%d', spage) = spage
	AND year BETWEEN 1846 AND 1870
	AND authors IS NOT NULL
	AND authors != 'The Natural History Secretary'
	AND authors != 'The Secretary'
	AND authors != 'The Secretaries'
	ORDER BY volume, issue, CAST(spage AS SIGNED)";
	
	// AND volume IN ('XIII','XIV','XV')
	
	$sql = "select * from publications 
	WHERE issn='0368-1068' 
	AND printf('%d', spage) = spage
	AND year BETWEEN 1902 AND 1908
	ORDER BY volume, issue, CAST(spage AS SIGNED)";
	
	
}




$data = do_query($sql);

foreach ($data as $obj)
{
	$csl = data_to_csl($obj);
	
	//print_r($csl);

	// Multiple languages?
	$sql = 'SELECT * FROM `multilingual` WHERE guid="' . $obj->guid . '"';
	
	$multilingual_data = do_query($sql);	
	foreach ($multilingual_data as $mdata)
	{
		switch ($mdata->key)
		{
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
	
	echo csl_to_ris($csl) . "\n\n";
}

?>
