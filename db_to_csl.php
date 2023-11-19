<?php

// convert database row to CSL
function data_to_csl($obj)
{
	$csl = new stdclass;
	
	foreach ($obj as $k => $v)
	{
		switch ($k)
		{
			case 'guid':
				$csl->id = $v;
				break;
		
			case 'type':
			case 'volume':
			case 'issue':
				$csl->{$k} = $v;
				break;
				
			case 'title':
				$v = html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
				$v = strip_tags($v);
				$csl->{$k} = $v;
				$csl->{$k} = preg_replace('/\.$/', '', $csl->{$k});
				break;				
				
			case 'spage':
				if (!isset($csl->page))
				{
					$csl->page = $v;
				}
				else
				{
					$csl->page = $v . '-' . $csl->page;
				}
				break;

			case 'epage':
				if (!isset($csl->page))
				{
					$csl->page = $v;
				}
				else
				{
					$csl->page .= '-' . $v;
				}
				break;
				
			case 'journal':
				$csl->{'container-title'} = $v;
				break;
				
			case 'authors':			
				if (isset($obj->authors_structured))
				{
					$csl->author = json_decode($obj->authors_structured);
				}
				else
				{			
					$csl->author = array();
					$parts = explode(';', $v);
					foreach ($parts as $name)
					{
						$author = new stdclass;
						$author->literal = $name;
						$csl->author[] = $author;
					}
				}				
				break;
				
			case 'date':
				$parts = explode('-', $obj->date);
	
				$csl->issued = new stdclass;
				$csl->issued->{'date-parts'} = array();
				$csl->issued->{'date-parts'}[0] = array();
				$csl->issued->{'date-parts'}[0][] = (Integer)$parts[0];
				if ($parts[1] != '00')
				{		
					$csl->issued->{'date-parts'}[0][] = (Integer)$parts[1];
				}
				if ($parts[2] != '00')
				{		
					$csl->issued->{'date-parts'}[0][] = (Integer)$parts[2];
				}	
				break;
				
			case 'year':
				if (!isset($csl->issued))
				{
					$csl->issued = new stdclass;
					$csl->issued->{'date-parts'} = array();
					$csl->issued->{'date-parts'}[0] = array();						
				}
				$csl->issued->{'date-parts'}[0][] = $v;
				break;
				
			case 'issn':
			case 'eissn':
				if (!isset($csl->ISSN))
				{
					$csl->ISSN = array();
				}
				$csl->ISSN[] = $v;
				break;				
				
			case 'doi':
				$csl->DOI = $v;
				break;
				
			case 'doi_agency':
				$csl->doi_agency = $v;
				break;				
				
			case 'cnki':
				$csl->CNKI = $v;
				break;				

			case 'handle':
				$csl->HANDLE = $v;
				break;

			case 'url':
				$csl->URL = $v;
				
				if (preg_match('/www.zobodat.at\/publikation_articles.php\?id=(?<id>\d+)/', $csl->URL, $m))
				{
					$csl->ZOBODAT = $m['id'];
				}

				if (preg_match('/dialnet.unirioja.es\/servlet\/articulo\?codigo=(?<id>\d+)/', $csl->URL, $m))
				{
					$csl->DIALNET = $m['id'];
				}

				if (preg_match('/jstor.org\/stable\/(?<id>\d+)/', $csl->URL, $m))
				{
					$csl->JSTOR = $m['id'];
				}
				
				if (preg_match('/dl.ndl.go.jp\/pid\/(?<id>\d+)/', $csl->URL, $m))
				{
					$csl->NDL = $m['id'];
				}

				break;

			case 'pdf':
				$csl->link = array();	
				$link = new stdclass;
				$link->URL = $obj->pdf;
				$link->{'content-type'} = "application/pdf";
	
				$csl->link[] = $link;					
				break;
				
			case 'waybackmachine':
				$csl->WAYBACK = $v;
				break;

			case 'internetarchive':
				$csl->ARCHIVE = $v;
				break;
				
			case 'license':
				$csl->copyright = $v;
				break;
				
						
			default:
				break;
		}
	}
	
	return $csl;
}

?>
