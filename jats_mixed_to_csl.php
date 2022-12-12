<?php

// Extract citations as text strings. This is useful if XML markup is incomplete

//----------------------------------------------------------------------------------------
// Recursively traverse DOM and process tags
function dive($dom, $node, &$text)
{	
	
	if ($node->nodeName == '#text')
	{
		$text[] = $node->nodeValue;
	}
		
	if ($node->hasChildNodes())
	{
		foreach ($node->childNodes as $children) 
		{
			dive ($dom, $children, $text);
		}
	}
}



//----------------------------------------------------------------------------------------
// Extract mixed citations and parse
function jats_mixed_to_csl($xml)
{
	$bibliography = array();

	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);

	$xpath->registerNamespace('xlink', 'http://www.w3.org/1999/xlink');

	/*
	// DOI of parent article
	$work_doi = '';

	$xpath_query = '//article/front/article-meta/article-id[@pub-id-type="doi"]';
	$nodeCollection = $xpath->query ($xpath_query);
	foreach($nodeCollection as $node)
	{
		$work_doi = $node->firstChild->nodeValue;
	}
	*/

	$xpath_query = '//back/ref-list/ref';
	$nodeCollection = $xpath->query ($xpath_query);
	foreach($nodeCollection as $node)
	{
		//echo $node->textContent . "\n";
	
		//echo $node->textContent . "\n";
		
		// we need to grab the content and not everything will be tagged :(
		
		$text = array();
		
		dive($dom, $node, $text);
		
		$citation = join(' ', $text);
		
		$citation = trim($citation);
		$citation = preg_replace('/\(\s+/', '(', $citation);
		$citation = preg_replace('/\s+\)/', ')', $citation);
		$citation = preg_replace('/\s\s+/', ' ', $citation);
		$citation = preg_replace('/ – /', '–', $citation);
		$citation = preg_replace('/\s+\./', '.', $citation);
		
		$bibliography[] = $citation;
	
	
	}
	
	return $bibliography;
}

?>
