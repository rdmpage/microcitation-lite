<?php

require_once (dirname(__FILE__) . '/csl_utils.php');

require_once (dirname(__FILE__) . '/HtmlDomParser.php');

use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL 				=> $url,
	  CURLOPT_FOLLOWLOCATION	=> TRUE,
	  CURLOPT_RETURNTRANSFER 	=> TRUE,
	  
	  CURLOPT_HEADER 			=> FALSE,
	  
	  CURLOPT_SSL_VERIFYHOST	=> FALSE,
	  CURLOPT_SSL_VERIFYPEER	=> FALSE,
	  
	  CURLOPT_COOKIEJAR			=> sys_get_temp_dir() . '/cookies.txt',
	  CURLOPT_COOKIEFILE		=> sys_get_temp_dir() . '/cookies.txt',
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type, 
			"Accept-Language: en-gb",
			"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 
		);
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	// echo $data;
	
	return $data;
}

$url = 'https://www.airitilibrary.com/Publication/Information?publicationID=P20170120001&type=%E6%9C%9F%E5%88%8A&tabName=2&issueYear=1995&issueID=201702070024&publisherID=U20161228001&isPrePrint=&SessionID=';


$command = "curl_chrome116 -k -L  -o 'x.html' '$url'";

system($command);

?>



