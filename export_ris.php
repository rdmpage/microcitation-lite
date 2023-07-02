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


if (0)
{
	// Journal of the Asiatic Society of Bengal

	$sql = "select * from publications 
	WHERE issn='0368-1068' 
	AND printf('%d', spage) = spage
	AND year='1908'";

	$sql = "select * from publications where issn='0368-1068' and authors like '%Moore%'";
	//$sql = "select * from publications where issn='0368-1068' and title like '%blat%'";
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
	
	//print_r($csl);
	
	echo csl_to_ris($csl) . "\n\n";
}

?>
