<?php

// Import DOIs as SQL

require_once (dirname(__FILE__) . '/csl_utils.php');

require_once (dirname(__FILE__) . '/HtmlDomParser.php');

use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type 
		);		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------
function doi_to_agency(&$prefix_to_agency, $prefix, $doi)
{
	$agency = '';
			
	if (isset($prefix_to_agency[$prefix]))
	{
		$agency = $prefix_to_agency[$prefix];
	}
	else
	{
		$url = 'https://doi.org/ra/' . $doi;	
		$json = get($url);
		$obj = json_decode($json);	
		if ($obj)
		{
			if (isset($obj[0]->RA))
			{
				$agency = $obj[0]->RA;		
				$prefix_to_agency[$prefix] = $agency;
			}	
		}
	}
	
	return $agency;
}

//----------------------------------------------------------------------------------------
function cnki_doi($doi)
{
	$csl = null;
	
	$html = get('https://doi.org/' . $doi);
	
	$dom = HtmlDomParser::str_get_html($html);
	
	if ($dom)
	{
		$csl = new stdclass;
		
		$csl->type = 'journal-article';
		
		// title
		foreach ($dom->find('h1') as $h1)
		{			
			$csl->title = $h1->plaintext;
		}
		
		// authors
		foreach ($dom->find('h3[class=author] span') as $span)
		{			
			$author = new stdclass;
			$author->literal = $span->plaintext;
			$author->literal = preg_replace('/[0-9,]/', '', $author->literal);
			$author->literal = preg_replace('/\s+$/', '', $author->literal);
			$csl->author[] = $author;
		}
		
		// abstract
		foreach ($dom->find('div[class=row] span[class=abstract-text]') as $span)
		{
			$csl->abstract = $span->plaintext;
		}
		
		// DOI
		foreach ($dom->find('div[class=row] ul li[class=top-space]') as $li)
		{		
			if (preg_match('/DOI：<\/span><p>(?<doi>.*)<\/p>/', $li->outertext, $m))	
			{
				$csl->DOI = $m['doi'];
			}
		}
		
		// collation
		foreach ($dom->find('div[class=top-tip] span a') as $a)
		{		
			if (isset($a->onclick))
			{
				if (preg_match('/getKns8NaviLink/', $a->onclick, $m))	
				{
					$csl->{'container-title'} = $a->plaintext;
					
					switch ($csl->{'container-title'})
					{
						case 'Asian Herpetological Research':
							$csl->ISSN[0] = '2095-0357';
							break;
					
						default:
							break;
					}
				}
				
				if (preg_match('/getKns8YearNaviLink/', $a->onclick))	
				{
					if (preg_match('/(?<year>[0-9]{4}),(?<volume>\d+)\(?0(?<issue>\d+)\)/', $a->plaintext, $m))	
					{
						$csl->issued = new stdclass;
						$csl->issued->{'date-parts'} = array();
						$csl->issued->{'date-parts'}[0] = array();
						$csl->issued->{'date-parts'}[0][] = (Integer)$m['year'];
											
						$csl->volume = $m['volume'];
						$csl->issue = $m['issue'];
				
					}
					
				}
				
			}
		}
		
		/*
		<p class="total-inform"><span>
          下载：43</span><span>
          页码：167-180</span><span>
          页数：14</span><span>
          大小：5789K</span></p>
          */
		foreach ($dom->find('p[class=total-inform] span') as $span)
		{		
			if (preg_match('/页码：(?<page>\d+-\d+)/u', $span->plaintext, $m))
			{
				$csl->page = $m['page'];
			}
		}          
		
		// CNKI
		foreach ($dom->find('input[id=paramfilename]') as $input)
		{		
			$csl->CNKI = $input->value;
		}
	}
	
	return $csl;
}

//----------------------------------------------------------------------------------------
function china_doi($doi)
{
	$csl = null;
	
	$html = get('https://doi.org/' . $doi);
	
	$dom = HtmlDomParser::str_get_html($html);
	
	if ($dom)
	{
		$csl = new stdclass;
		
		$csl->type = 'journal-article';
	
		foreach ($dom->find('tr') as $tr)
		{
				
			$count = 1;

			foreach ($tr->find('td[class=title1]') as $td)
			{
				if ($count % 2 == 1)
				{
					$key = $td->plaintext;
					$key = str_replace('&nbsp;', ' ', $key);
					$key = trim($key);
				}
				else
				{				
					$value = $td->plaintext;
					$value = str_replace('&nbsp;', ' ', $value);
					$value = trim($value);
				
					$value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
				
					// echo $key . '=' . $value . "\n";
		
					switch ($key)
					{
						case 'Volume':
						case 'Issue':
							$csl->{strtolower($key)} = $value;
							break;
												
						case 'Year':
							$csl->issued = new stdclass;
							$csl->issued->{'date-parts'}[0] = array(
		   						(Integer)$value
		   					);  
							break;	
						
						case 'Journal：':
							$csl->{'container-title'} = $value;
						
							$issn = '';
							switch ($value)
							{
								case 'Acta Arachnologica Sinica':
									$issn = '1005-9628';
									break;
							
								case 'ATCA PALAEONTOLOGICA SINICA':
									$issn = '0001-6616';
									break;
								
								case 'Journal of Biosafety':
									$issn = '2095-1787';
									break;

								case 'Zoological Systematics':
									$issn = '1000-0739';
									break;
						
								default:
									break;
							}
						
							if ($issn != '')
							{
								$csl->ISSN[] = $issn;
							}						
							break;						
						
						case 'doi：':
							$csl->DOI = $value;							
							$csl->URL = 'http://www.chinadoi.cn/portal/mr.action?doi=' . $value;
							break;
						
						case 'Title：':
							if (trim($value) != "")
							{
								// should be English but may not be
								$language = 'en';
								if (preg_match('/\p{Han}+/u', $value))
								{
									$language = 'zh';
								}	
							
								if (!isset($csl->title))
								{
									$csl->title = $value;
								}

								// multi
								if (!isset($csl->multi))
								{
									$csl->multi = new stdclass;
									$csl->multi->_key = new stdclass;					
								}
								if (!isset($csl->multi->_key->title))
								{
									$csl->multi->_key->title = new stdclass;					
								}
								$csl->multi->_key->title->{$language} = $value;
							}
							break;
						
						case '题 名：':
							// should be Chinese but may not be
							$language = 'zh';
							if (!preg_match('/\p{Han}+/u', $value))
							{
								$language = 'en';
							}							
						
							if (!isset($csl->title))
							{
								$csl->title = $value;
							}

							// multi
							if (!isset($csl->multi))
							{
								$csl->multi = new stdclass;
								$csl->multi->_key = new stdclass;					
							}
							if (!isset($csl->multi->_key->title))
							{
								$csl->multi->_key->title = new stdclass;					
							}
							$csl->multi->_key->title->{$language} = $value;
							break;
						
						case '第一作者：':
							$author = new stdclass;
							$author->literal = $value;
							$csl->author = array($author);
							break;
		
						default:
							break;
					}
		
				}
				$count++;
			}
		}
	}
	
	return $csl;
}
			

//----------------------------------------------------------------------------------------
$prefix_filename = dirname(__FILE__) . '/prefix.json';

if (file_exists($prefix_filename))
{
	$json = file_get_contents($prefix_filename);
	$prefix_to_agency = json_decode($json, true);
}
else
{
	$prefix_to_agency = array();
}

$dois=array();

$dois=array(
'10.5635/ASED.2012.28.4.230',
);


$dois = array(
'10.3406/BSEF.1996.17243',
'10.11646/zootaxa.1390.1.3',
'10.21248/contrib.entomol.23.1-4.57-69', 	// DataCite
'10.19269/sugapa2020.13(1).01', 			//mEDRA
'10.11238/mammalianscience.58.175', 		// JaLC
);

$dois=array(
//'10.3969/j.issn.1005-9628.2018.01.07', // WanFang
//'10.16373/j.cnki.ahr.200084',

//'10.3969/j.issn.1005-9628.2022.01.006',
//'10.3969/j.issn.1005-9628.2019.01.004',

'10.3969/j.issn.1005-9628.2019.01.004',
);

$count = 1;

foreach ($dois as $doi)
{
	// DOI prefix
	$parts = explode('/', $doi);
	$prefix = $parts[0];
	
	// Agency lookup
	$agency = doi_to_agency($prefix_to_agency, $prefix, $doi);
	
	$doi = strtolower($doi);
	
	switch ($agency)
	{
		case 'CNKI':
			$obj = cnki_doi($doi);		
			break;
	
		case 'ISTIC':
			$obj = china_doi($doi);		
			break;
	
		default:
			$url = 'https://doi.org/' . $doi;	
			$json = get($url, 'application/vnd.citationstyles.csl+json');
			$obj = json_decode($json);
			break;		
	}
	
	if ($obj)	
	{
		if ($agency != '')
		{
			$obj->doi_agency = $agency;
		}
	
		$sql = csl_to_sql($obj, 'publications_doi');		
		echo $sql . "\n";
	}
	
	// Give server a break every 10 items
	if (($count++ % 5) == 0)
	{
		$rand = rand(1000000, 3000000);
		echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}	
}

// save prefix file
file_put_contents($prefix_filename, json_encode($prefix_to_agency, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

?>
