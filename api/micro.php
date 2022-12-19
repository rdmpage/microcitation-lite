<?php

// Do a range search in our local SQLite database

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/api_utilities.php');

$pdo = new PDO('sqlite:../microcitation.db');

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

$doc = null;
$doc = http_post_endpoint(["container-title", "ISSN", "volume", "page"]);

if (0)
{
	$json = '{
	"container-title": "Berl. ent. Z.",
	"ISSN" : ["0323-6145"],
    "volume": "11",
    "page": 320
	
	}';
	
	$doc = json_decode($json);
}

$doc->status = 404;

$keys = array("container-title", "ISSN", "volume", "page", "author", "issued");

$parameters = array();
foreach ($keys as $k)
{
	if (isset($doc->{$k}))
	{
		switch ($k)
		{
			case 'container-title':
				$parameters['title'] = $doc->{$k};
				break;

			case 'ISSN':
				$parameters['issn'] = $doc->{$k}[0];
				break;
				
			case 'volume':
				$parameters['volume'] = $doc->{$k};
				break;

			case 'issued':
				if (isset($doc->{$k}->{'date-parts'}))
				{
					$parameters['date'] = $doc->{$k}->{'date-parts'}[0][0];
				}
				break;
				
			case 'author':
				if (isset($doc->{$k}[0]->literal))
				{
					$parameters['author'] = $doc->{$k}[0]->literal;
				}
				break;				

			case 'page':
				$parameters['page'] = $doc->{$k};
				break;
				
			default:
				break;
		
		}		
	}
}

$sql = 'SELECT * ';
//$sql .= 'FROM publications_doi ';
$sql .= 'FROM publications ';
$sql .= 'WHERE issn="' . $parameters['issn'] . '" ';

if (isset($parameters['author']))
{
	$sql .= 'AND authors LIKE "%' . $parameters['author'] . '%" ';
}

$sql .= ' AND ' . $parameters['page'] . ' BETWEEN CAST(spage AS INT) AND CAST(epage AS INT)';

// Some journals have changed volume numbering so we may have to rely on other tricks
$use_volume = true;

// special cases
if ($parameters['issn'] == '0035-8894' && isset($parameters['date']) 
&& $parameters['date'] == (Int)$parameters['volume']
)
{
	$sql .= ' AND doi LIKE "%.' . $parameters['date'] . '.%"';
	$use_volume = false;
}

if ($use_volume)
{
	$sql .= 'AND volume="' . $parameters['volume'] . '" ';
}



$doc->sql = $sql;


$data = do_query($sql);

foreach ($data as $obj)
{	
	if (isset($obj->doi))	
	{
		if (!isset($doc->DOI))
		{
			$doc->DOI = array();
		}
		$doc->DOI[] = $obj->doi;
	}

	if (isset($obj->url))	
	{
		if (!isset($doc->URL))
		{
			$doc->URL = array();
		}
		$doc->URL[] = $obj->url;
	}
	
	if (isset($obj->wikidata))	
	{
		if (!isset($doc->WIKIDATA))
		{
			$doc->WIKIDATA = array();
		}
		$doc->WIKIDATA[] = $obj->wikidata;
	}
	
}


/*
if (count($data) == 1)
{
	if (isset($data[0]->doi))
	{
		$doc->DOI = $data[0]->doi;
	}
}
*/


if (isset($doc->DOI) || isset($doc->URL) || isset($doc->WIKIDATA))
{
	$doc->status = 200;
}

send_doc($doc);	

?>
