<?php

// Extract citations from JATS XML

//----------------------------------------------------------------------------------------
function jats_to_csl($xml)
{
	$bibliography = array();

	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);

	$xpath->registerNamespace('xlink', 'http://www.w3.org/1999/xlink');

	// DOI of parent article
	$work_doi = '';

	$xpath_query = '//article/front/article-meta/article-id[@pub-id-type="doi"]';
	$nodeCollection = $xpath->query ($xpath_query);
	foreach($nodeCollection as $node)
	{
		$work_doi = $node->firstChild->nodeValue;
	}

	$xpath_query = '//back/ref-list/ref';
	$nodeCollection = $xpath->query ($xpath_query);
	foreach($nodeCollection as $node)
	{
		if ($node->hasAttributes()) 
		{ 
			$attributes = array();
			$attrs = $node->attributes; 
		
			foreach ($attrs as $i => $attr)
			{
				$attributes[$attr->name] = $attr->value; 
			}
		
			$key = $attributes['id'];
		}
	
		$citation = new stdclass;
	
		// default
		$citation->type = 'article-journal';
	
		$citation->id = '#' . $key;
		
		if ($work_doi != '')
		{
			$citation->id = $work_doi . $citation->id;
		}
	
		$citation->author = array();
		$citation->editor = array();	
	
		// (mixed-citation|nlm-citation)
	
		$citation->unstructured = $node->nodeValue;
		$citation->unstructured = trim($citation->unstructured);

		// authors------------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/person-group/name', $node);
		foreach($nc as $n)
		{
			$author = new stdclass;
		
			$parts = array();
		
			$ncc = $xpath->query ('given-names', $n);
			foreach($ncc as $nc)
			{
				$author->given = $nc->firstChild->nodeValue;
				$author->given = preg_replace('/([A-Z])([A-Z])/u', '$1 $2', $author->given);
				$author->given = trim($author->given);
			}
			$ncc = $xpath->query ('surname', $n);
			foreach($ncc as $nc)
			{
				$author->family = $nc->firstChild->nodeValue;
			}

			$citation->author[] = $author;
		}
		
		// PLoS is flatter
		// authors------------------------------------------------------------------------
		$nc = $xpath->query ('mixed-citation/name', $node);
		foreach($nc as $n)
		{
			$author = new stdclass;
		
			$parts = array();
		
			$ncc = $xpath->query ('given-names', $n);
			foreach($ncc as $nc)
			{
				$author->given = $nc->firstChild->nodeValue;
				$author->given = preg_replace('/([A-Z])([A-Z])/u', '$1 $2', $author->given);
				$author->given = trim($author->given);
			}
			$ncc = $xpath->query ('surname', $n);
			foreach($ncc as $nc)
			{
				$author->family = $nc->firstChild->nodeValue;
			}

			$citation->author[] = $author;
		}
			
	
		// title--------------------------------------------------------------------------
	   	$nc = $xpath->query ('(mixed-citation|nlm-citation)/article-title', $node);
		foreach($nc as $n)
		{
			$citation->title = trim($n->textContent);
		}
	
		// container----------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/source', $node);
		foreach($nc as $n)
		{
			$citation->{'container-title'} = trim($n->textContent);
		}
	
		// publisher----------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/publisher-name', $node);
		foreach($nc as $n)
		{
			$citation->{'publisher'} = $n->nodeValue;
			$citation->type = 'book';
		}

		$nc = $xpath->query ('(mixed-citation|nlm-citation)/publisher-loc', $node);
		foreach($nc as $n)
		{
			$citation->{'publisher-place'} = $n->nodeValue;
		} 
	
		// date---------------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/year', $node);
		foreach($nc as $n)
		{
			$year = $n->firstChild->nodeValue;
			$year = preg_replace('/[a-z]/', '', $year);
		
			$citation->issued = new stdclass;
			$citation->issued->{'date-parts'} = array();
			$citation->issued->{'date-parts'}[0][] = (Integer)$year;	
	   }
	
		// volume-------------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/volume', $node);
		foreach($nc as $n)
		{
			$citation->volume = $n->firstChild->nodeValue;
		}
 
		// issue--------------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/issue', $node);
		foreach($nc as $n)
		{
			$citation->issue = $n->firstChild->nodeValue;
		}

		// title--------------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/fpage', $node);
		foreach($nc as $n)
		{
			$citation->page = $n->firstChild->nodeValue;
		}

		// pagination---------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/lpage', $node);
		foreach($nc as $n)
		{
			if (isset($citation->page))
			{
				$citation->page .= '-';
			}
			else
			{
				$citation->page = '';
			}
			$citation->page .= $n->firstChild->nodeValue;
		}
   
		// DOI----------------------------------------------------------------------------
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/ext-link[@ext-link-type="doi"]/@xlink:href', $node);
		foreach($nc as $n)
		{
			$citation->DOI = strtolower($n->firstChild->nodeValue);
		}

		$nc = $xpath->query ('(mixed-citation|nlm-citation)/pub-id[@pub-id-type="doi"]', $node);
		foreach($nc as $n)
		{
			$citation->DOI = strtolower($n->firstChild->nodeValue);
		}		

		// URL----------------------------------------------------------------------------		
		$nc = $xpath->query ('(mixed-citation|nlm-citation)/ext-link[@ext-link-type="uri"]/@xlink:href', $node);
		foreach($nc as $n)
		{
			$citation->URL = $n->firstChild->nodeValue;
		}
	
		// cleanup
		if (count($citation->editor) == 0)
		{
			unset($citation->editor);
		}
		
		// articles with publishers are probably books
		if (($citation->type == 'article-journal') 
			&& (isset($citation->publisher)
			||  isset($citation->{'publisher-place'}))
			)
		{
			if (isset($citation->{'container-title'}) && !isset($citation->title))
			{
				$citation->type = 'book';
			}		
		}		
				
		if ($citation->type == 'book')
		{
			// both title and chapter suggests a book chapter
			if (isset($citation->{'container-title'}) && isset($citation->title))
			{
				$citation->type = 'chapter';
			}

			// no title but a container-title? move it!
			if (isset($citation->{'container-title'}) && !isset($citation->title))
			{
				$citation->title = $citation->{'container-title'};
				unset($citation->{'container-title'});
			}
		
		
		}
		
		 
		// clean for debugging
		unset($citation->unstructured);
		
		$bibliography[] = $citation;
	
	
	}
	
	return $bibliography;
}

?>
