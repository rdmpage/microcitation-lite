<?php

use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor;

require 'vendor/autoload.php';

require_once (dirname(__FILE__) . '/transtab_latex_unicode.inc.php');
require_once (dirname(__FILE__) . '/nameparse.php');


//--------------------------------------------------------------------------------------------------
function to_unicode($str)
{
	global $transtab_latex_unicode;
	
	foreach ($transtab_latex_unicode as $x => $y)
	{
		if (preg_match('/' . $x . '/', $str))
		{
			$str = preg_replace('/' . $x . '/', $y, $str);
		}
	}
	
	return $str;
}

//--------------------------------------------------------------------------------------------------

// test

$bibtex = <<<BIBTEX
@conference {38,
	title = {Ciempi{\'e}s (Chilopoda) hipogeos de Cuba: Una actualizaci{\'o}n necesaria.},
	booktitle = {XII Convenci{\'o}n de Medio Ambiente y Desarrollo},
	year = {2019},
	month = {07/2019},
	publisher = {Editorial AMA},
	organization = {Editorial AMA},
	address = {La Habana, Cuba},
	keywords = {an{\'a}lisis de vac{\'\i}os, ciempi{\'e}s, colecciones biol{\'o}gicas, conservaci{\'o}n, end{\'e}micos locales},
	isbn = {978-959-300-145-8},
	author = {Carlos A. Mart{\'\i}nez-Mu{\~n}oz}
}

BIBTEX;


$listener = new Listener();
$listener->addProcessor(new Processor\TagNameCaseProcessor(CASE_LOWER));

// Create a Parser and attach the listener
$parser = new Parser();
$parser->addListener($listener);

if (0)
{
	$parser->parseString($bibtex);
}
else
{
	$filename = 'Biblio-Bibtex.bib';
	$parser->parseFile($filename);
}

$entries = $listener->export();

foreach ($entries as $entry)
{
	// print_r($entry);

	$reference = new stdclass;
	
	foreach ($entry as $k => $v)
	{
		$v = to_unicode($v);
	
		switch ($k)
		{
			case 'title':
				$reference->title = $v;
				$reference->title  = strip_tags($reference->title );				
				break;
			
			case 'volume':
				$reference->{$k} = $v;
				break;

			case 'number':
				$reference->issue = $v;
				break;

			case 'pages':
				$reference->page = $v;
				break;
				
			case 'booktitle':
			case 'journal':
				$reference->{'container-title'} = $v;
				break;
								
			case 'issn':
				if (preg_match('/([0-9]{4}-[0-9]{3}([0-9]|X))/', $v, $m))
				{
					$reference->ISSN[] = $m[1];
				}
				break;

			case 'isbn':
				$reference->ISBN[] = $v;
				break;
				
			case 'url':
				$reference->URL = $v;
				break;

			case 'doi':
				$reference->DOI = $v;
				$reference->DOI = preg_replace('/https?:\/\/(dx\.)?doi\.org\//', '' , $reference->DOI);
				break;
				
			case 'type':
				switch ($v)
				{
					case 'article':
						$reference->type = 'article-journal';
						break;
						
					default:
						$reference->type = $v;
						break;	
				}
				break;
				
			case 'year':
				$reference->issued = new stdclass;
				$reference->issued->{'date-parts'} = array();
				$reference->issued->{'date-parts'}[0] = array($v);
				break;

			case 'publisher':
				$reference->$k = $v;
				break;
				
			case 'author':

				$names = explode(' and ', $v);
				
				foreach ($names as $name)
				{				
					$author = new stdclass;
				
					// Get parts of name
					$parts = parse_name($name);
				
					if (isset($parts['last']))
					{
						$author->family = $parts['last'];
					}
				
					if (isset($parts['suffix']))
					{
						$author->suffix = $parts['suffix'];
					}
				
					if (isset($parts['first']))
					{
						$author->given = $parts['first'];
					
						if (array_key_exists('middle', $parts))
						{
							$author->given .= ' ' . $parts['middle'];
						}
					}
					//$author->given = preg_replace('/\./Uu', '', $author->given);
					//$author->literal = $author->given . ' ' . $author->family;
					
					if (!isset($author->family) && isset($author->given))
					{
						$author->literal = $author->given;
						unset($author->given);
					}
				
					$reference->author[] = $author;
				}
				break;
								
			default:
				break;
			
		}
	
	}
	
	print_r($reference);
}



?>

