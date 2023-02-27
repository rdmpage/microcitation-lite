<?php

error_reporting(E_ALL);
require_once (dirname(__FILE__) . '/HtmlDomParser.php');

use Sunra\PhpSimple\HtmlDomParser;

$cache_dir = dirname(__FILE__) . '/cache-wanfang';

$count = 1;

//----------------------------------------------------------------------------------------
function get($url, $user_agent='', $content_type = '')
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
			"Accept: " . $content_type, 
			"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 
		);
		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	
	$http_code = $info['http_code'];
	
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------
function get_html_from_wanfang($wanfang_id)
{
	global $cache_dir;
	global $count;

	$wanfang_filename = $wanfang_id;
	$url = 'http://med.wanfangdata.com.cn/Paper/Detail/PeriodicalPaper_' . $wanfang_id;
		
	$wanfang_filename .= '.html';
	
	$wanfang_filename = $cache_dir . '/' . $wanfang_filename;
	
	if (!file_exists($wanfang_filename))
	{
		$count++;
		
		$html = get($url, "text/html");	
		file_put_contents($wanfang_filename, $html);	
	}
		
	$html = file_get_contents($wanfang_filename);
	
	return $html;
}

//----------------------------------------------------------------------------------------

$ids = array(
//'hdkcxb201103007',
//'hdkcxb200101001',

'zxxb202201006',
'zxxb202201007',
);

$ids = array();

for ($year = 2019; $year <= 2023; $year++)
{
	for ($issue = 1; $issue <= 2; $issue++)
	{
		for ($article = 1; $article <= 25; $article++)
		{
			$id = 'zxxb' . $year .  str_pad($issue, 2, '0', STR_PAD_LEFT) . str_pad($article, 3, '0', STR_PAD_LEFT);
			$ids[] = $id;
		}
	}
}

$ids = array();

for ($year = 1998; $year <= 2000; $year++)
{
	for ($issue = 1; $issue <= 2; $issue++)
	{
		for ($article = 1; $article <= 25; $article++)
		{
			$id = 'zxxb' . $year .  str_pad($issue, 2, '0', STR_PAD_LEFT) . str_pad($article, 3, '0', STR_PAD_LEFT);
			$ids[] = $id;
		}
	}
}


$ids=array(
'zxxb200401004'
);


foreach ($ids as $id)
{
	$html = get_html_from_wanfang($id);
	
	// parse
	$dom = HtmlDomParser::str_get_html($html);

	if ($dom)
	{
		$csl = new stdclass;
		$csl->type = 'article-journal';
		
		$csl->URL = 'http://med.wanfangdata.com.cn/Paper/Detail/PeriodicalPaper_' . $id;
		
		// 
		/*
		if (preg_match('/zxxb(?<year>[0-9]{4})(?<issue>[0-9]{2})/', $id, $m))
		{
			$csl->issue = $m['issue'];
			
			$csl->issued = new stdclass;
			$csl->issued->{'date-parts'} = array();	
			$csl->issued->{'date-parts'}[0][] = (Integer)$m['year'];	
			
			$csl->ISSN[] = '1005-9628';
			$csl->{'container-title'} = 'Acta Arachnologica Sinica';
		}
		*/
		
		foreach ($dom->find('div[class=headline]') as $div)
		{
			$csl->multi = new stdclass;
			$csl->multi->_key = new stdclass;
			$csl->multi->_key->title = new stdclass;
		
			foreach ($div->find('h2') as $h2)
			{
				$language = 'zh';
				$csl->multi->_key->title->{$language} = $h2->plaintext;
				
				$csl->title = $h2->plaintext;
			}
			
			foreach ($div->find('h3') as $h3)
			{
				$language = 'en';
				$csl->multi->_key->title->{$language} = html_entity_decode($h3->plaintext, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}
		
		}
		
		foreach ($dom->find('div[class=table-tr]') as $div)
		{
			$key = '';
			$value = '';
		
			foreach ($div->find('span[class=table-th]') as $th)
			{
				$key = trim($th->plaintext);
			}
			
			foreach ($div->find('span[class=table-td]') as $td)
			{
				$value = trim($td->plaintext);
			}
			
			switch ($key)
			{
				case 'DOI：':
					$csl->DOI = $value;
					break;
					
					/*
					// released date
				case '发布时间：':
					$csl->issued = new stdclass;
					$csl->issued->{'date-parts'} = array();
					
					$parts = preg_split('/[-|\/]/', $value);				
					foreach ($parts as $part)
					{
						$part = preg_replace('/^0/', '', $part);
						$csl->issued->{'date-parts'}[0][] = (Integer)$part;
					}				
					break;
					*/
					
				case '期刊：':
					if (preg_match('/《(?<journal>.*)》\s*(?<year>[0-9]{4})年(?<volume>\d+)卷(?<issue>\d+)期\s+(?<spage>\d+)-(?<epage>\d+)/u', $value, $m))
					{
						$csl->{'container-title'} = $m['journal'];
						$csl->volume = $m['volume'];
						$csl->issue = $m['issue'];						
						$csl->page = $m['spage'] . '-' . $m['epage'];						
						
						switch ($m['journal'])
						{
							case '蛛形学报':
								$csl->ISSN[] = '1005-9628';
						
							default:
								break;
						}
						
						$csl->issued = new stdclass;
						$csl->issued->{'date-parts'} = array();	
						$csl->issued->{'date-parts'}[0][] = (Integer)$m['year'];
					}
					break;
					
				case '作者：':
					if (preg_match_all('/([^\s]+)\s+\[\d+\]/', $value, $m))
					{
						$csl->author = array();
						foreach ($m[1] as $name)
						{
							$author = new stdclass;
							$author->literal = $name;
							$csl->author[] = $author;							
						}
					}
					break;
			
				default:
					break;
			}
		}
		
		if (isset($csl->title))
		{				
			//print_r($csl);
				
			echo json_encode($csl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";

		}
		
	}
	


}


?>
