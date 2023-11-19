<?php

error_reporting(E_ALL);

//----------------------------------------------------------------------------------------
// Convert a simple CSL object to RIS
function csl_to_ris($csl)
{
	$csl_ris_map  = array(
		'type'				=> 'TY',
	
		'title' 			=> 'TI',
		
		'author' 			=> 'AU',
				
		'container-title' 	=> 'JO',
		'ISSN' 				=> 'SN',
		
		'volume' 			=> 'VL',
		'issue' 			=> 'IS',
		
		'spage' 			=> 'SP',
		'epage' 			=> 'EP',
		
		'year' 				=> 'Y1',
		'date'				=> 'PY',
		
		'abstract'			=> 'N2',
		
		'URL'				=> 'UR',
		'DOI'				=> 'DO',
		'pdf'				=> 'L1',

		'publisher'			=> 'PB',
		'publisher-place'	=> 'PP',
		);

	$ris_keys = array_values($csl_ris_map);
	$ris_keys[] = 'ER';
	
	foreach ($csl as $k => $v)
	{
		switch ($k)
		{
			case 'type':
				switch ($v)
				{	
					case 'article-journal':
						$ris_values[$csl_ris_map[$k]][] = 'JOUR';		
						break;

					case 'book':
						$ris_values[$csl_ris_map[$k]][] = 'BOOK';		
						break;

					case 'chapter':
						$ris_values[$csl_ris_map[$k]][] = 'CHAP';		
						break;					
				
					default:
						$ris_values[$csl_ris_map[$k]][] = 'GEN';		
						break;
				}
				break;
				
			case 'title':
			case 'container-title':
			case 'volume':
			case 'issue':
			case 'DOI':
			case 'URL':
			case 'publisher':
			case 'publisher-place':
				$ris_values[$csl_ris_map[$k]][] = $v;				
				break;
				
			case 'page':
				if (preg_match('/(.*)-(.*)/', $v, $m))
				{
					$ris_values['SP'][] = $m[1];
					$ris_values['EP'][] = $m[2];						
				}
				else
				{
					$ris_values['SP'][] = $v;
				}
				break;
				
			case 'issued':
				$ris_values['Y1'][] = $v->{'date-parts'}[0][0];
				break;
				
			case 'ISSN':
				if (is_array($v))
				{
					$ris_values[$csl_ris_map[$k]][] = $v[0];	
				}
				else
				{
					
					$ris_values[$csl_ris_map[$k]][] = $v;
				}
				break;
								
			case 'author':
				foreach ($v as $author)
				{
					if (isset($author->literal))
					{
						$ris_values[$csl_ris_map[$k]][] = $author->literal;
					}
					else
					{
						$name_parts = array();
						if (isset($author->given))
						{
							$name_parts[] = $author->given;
						}
						if (isset($author->family))
						{
							$name_parts[] = $author->family;
						}
						$name = trim(join(' ', $name_parts));
						if ($name != '')
						{
							$ris_values[$csl_ris_map[$k]][] = $name;
						}
					}
				}
				break;
				
			case 'link':
				$have_pdf = false;				
				foreach ($v as $link)
				{
					//print_r($link);
					if (($link->{'content-type'} == 'application/pdf') && !$have_pdf)
					{
						$ris_values[$csl_ris_map['pdf']][] = $link->URL;
						$have_pdf = true;
					}
				}
				break;
				
			default:
				break;
		}

	
	}
	$ris_values['ER'][] = '';	
		
	//print_r($ris_values);
	
	$ris = '';
	
	foreach ($ris_keys as $k)
	{
		if (isset($ris_values[$k]))
		{
			foreach ($ris_values[$k] as $v)
			{
				$ris .= $k . '  - ' . $v . "\n";
			}
		
		}	
	}
	
	return $ris;
}

//----------------------------------------------------------------------------------------
// Convert a simple CSL object to OpenURL query
function csl_to_openurl($csl)
{
	$parameters = array();
	
	$csl_openurl_map  = array(
		'type'				=> 'TY',
	
		'title' 			=> 'TI',
		
		'author' 			=> 'rft.au',
				
		'container-title' 	=> 'JO',
		
		'ISSN' 				=> 'rft.issn',
		
		'collection-title'	=> 'rft.series',
		
		'volume' 			=> 'rft.volume',
		'issue' 			=> 'rft.issue',
		
		'spage' 			=> 'rft.spage',
		'epage' 			=> 'rft.epage',
		
		'issued' 			=> 'rft.date',
				
		'URL'				=> 'rft_id',
		'DOI'				=> 'rft_id',

		//'publisher'			=> 'PB',
		//'publisher-place'	=> 'PP',
		);
		
	$parameters['ctx_ver'][] = 'Z39.88-2004';
	
	foreach ($csl as $k => $v)
	{
		switch ($k)
		{
			case 'type':
				switch ($v)
				{	
					case 'article-journal':
						$parameters['rft_val_fmt'][] = 'info:ofi/fmt:kev:mtx:journal';					
						$parameters['genre'][] = 'article';
						
						if (isset($csl->title))
						{
							$parameters['rft.atitle'][] = $csl->title;
						}
						if (isset($csl->{'container-title'}))
						{
							$parameters['rft.jtitle'][] = $csl->{'container-title'};
						}						
						break;

					case 'book':
					case 'chapter':
					default:
						$parameters['genre'][] = $v;
						
						if (isset($csl->title))
						{
							$parameters['rft.title'][] = $csl->title;
						}
						break;
				}
				break;
								
			case 'collection-title':
			case 'volume':
			case 'issue':
			case 'URL':
				$parameters[$csl_openurl_map[$k]][] = $v;
				break;			
			
			case 'DOI':
				$parameters[$csl_openurl_map[$k]][] = 'info:doi/' . $v;
				break;
				
			case 'page':
				if (preg_match('/(.*)-(.*)/', $v, $m))
				{
					$parameters['rft.spage'][] = $m[1];
					$parameters['rft.epage'][] = $m[2];						
				}
				else
				{
					$parameters['rft.spage'][] = $v;
				}
				break;
				
			case 'issued':
				$parameters[$csl_openurl_map[$k]][] = $v->{'date-parts'}[0][0];
				break;
								
			case 'author':
				foreach ($v as $author)
				{
					if (isset($author->literal))
					{
						$parameters[$csl_openurl_map[$k]][] = $author->literal;
					}
					else
					{
						$name_parts = array();
						if (isset($author->given))
						{
							$name_parts[] = $author->given;
						}
						if (isset($author->family))
						{
							$name_parts[] = $author->family;
						}
						$name = trim(join(' ', $name_parts));
						if ($name != '')
						{
							$parameters[$csl_openurl_map[$k]][] = $name;
						}
					}
				}
				break;
				
			default:
				break;
		}

	
	}
	
	$kv = array();
	
	foreach ($parameters as $k => $v)
	{
		foreach ($v as $value)
		{
			$kv[] = $k . '=' . urlencode($value);
		}
	
	}
	
	$openurl = join('&', $kv);

	return $openurl;
}

//----------------------------------------------------------------------------------------
// Convert a simple CSL object to OpenURL query
function csl_to_tsv($csl)
{
	$parameters = array();
	
	$csl_tsv_map  = array(
		'id'				=> 'guid',
	
		'type'				=> 'type',
	
		'title' 			=> 'title',
		
		'author' 			=> 'authors',
				
		'container-title' 	=> 'container-title',
		
		'ISSN' 				=> 'issn',
		
		'collection-title'	=> 'series',
		
		'volume' 			=> 'volume',
		'issue' 			=> 'issue',
		
		'spage' 			=> 'spage',
		'epage' 			=> 'epage',
		
		'issued' 			=> 'date',
				
		'URL'				=> 'url',
		'DOI'				=> 'doi',

		'publisher'			=> 'publisher',
		'publisher-place'	=> 'publisher-place',
		);
		
	$tsv_keys = array_values($csl_tsv_map);
	
	$tsv = array();
	
	foreach ($csl as $k => $v)
	{
		switch ($k)
		{
			case 'id':
			case 'type':
			case 'title':
			case 'container-title':
			case 'collection-title':
			case 'volume':
			case 'issue':
			case 'URL':
			case 'DOI':
				$tsv[$csl_tsv_map[$k]] = trim($v);
				break;
				
			case 'page':
				if (preg_match('/(.*)-(.*)/', $v, $m))
				{
					$tsv['spage'] = $m[1];
					$tsv['epage'] = $m[2];						
				}
				else
				{
					$tsv['spage'] = $v;
				}
				break;
				
			case 'issued':
				$tsv[$csl_tsv_map[$k]] = $v->{'date-parts'}[0][0];
				break;
								
			case 'author':
				foreach ($v as $author)
				{
					$authors = array();
				
					if (isset($author->literal))
					{
						$authors[] = $author->literal;
					}
					else
					{
						$name_parts = array();
						if (isset($author->given))
						{
							$name_parts[] = $author->given;
						}
						if (isset($author->family))
						{
							$name_parts[] = $author->family;
						}
						$name = trim(join(' ', $name_parts));
						if ($name != '')
						{
							$authors[] = $name;
						}
					}
				}
				
				$tsv[$csl_tsv_map[$k]] = join(';', $authors);
				break;
				
			default:
				break;
		}	
	}
	
	
	$row = array();
	foreach ($tsv_keys as $k)
	{
		if (isset($tsv[$k]))
		{
			$row[] = $tsv[$k];
		}
		else
		{
			$row[] = "";
		}
	}
	
	$tsv_string = join("\t", $row);

	return $tsv_string;
}

//----------------------------------------------------------------------------------------
// Convert a simple CSL object to SQL
function csl_to_sql($csl, $table = "publications")
{
	$keys = array();
	$values = array();
	
	$guid = '';
	
	if ($guid == '')
	{
		if (isset($csl->DOI))
		{
			$guid = $csl->DOI;
		}
	}
	
	if ($guid == '')
	{
		if (isset($csl->HANDLE))
		{
			$guid = $csl->HANDLE;
		}
	}	
		
	if ($guid == '')
	{
		if (isset($csl->URL))
		{
			$guid = $csl->URL;
		}
	}	
	
	if ($guid == '')
	{
		if (isset($csl->link))
		{
			foreach ($csl->link as $link)
			{
				if (isset($link->{'content-type'}) && ($link->{'content-type'} == "application/pdf"))
				{
					$guid = $link->URL;
				}
			}
		}
	}	
	
	
	if ($guid == '')
	{
		$guid = uniqid();
	}

	$keys[] = 'guid';
	$values[] = '"' . $guid . '"';
	
	$multilingual_keys = array();
	$multilingual_values = array();
	
	$pdf = ''; // just want one PDF
	
	foreach ($csl as $k => $v)
	{
		switch ($k)
		{		
			case 'DOI':
				$keys[] ='doi';
				$values[] = '"' . $v . '"';	
				break;		

			case 'CNKI':
				$keys[] ='cnki';
				$values[] = '"' . $v . '"';	
				break;		

			case 'HANDLE':
				$keys[] ='handle';
				$values[] = '"' . $v . '"';	
				break;		

			case 'JSTOR':
				$keys[] ='jstor';
				$values[] = '"' . $v . '"';	
				break;		

			case 'URL':
				$keys[] ='url';
				$values[] = '"' . $v . '"';	
				break;		
				
			case 'doi_agency':	
				$keys[] = $k;
				$values[] = '"' . $v . '"';	
				break;	
			
			case 'type':
			case 'volume':
			case 'issue':
			case 'publisher':
			case 'abstract':
				$keys[] = $k;
				$values[] = '"' . str_replace('"', '""', $v) . '"';	
				break;	
	
			case 'container_title': // Zenodo fuck up
			case 'container-title':
				if (is_array($v))
				{
					if (count($v) > 0)
					{
						$keys[] = 'journal';
						$values[] = '"' . str_replace('"', '""', $v[0]) . '"';					
					}
				}
				else 
				{
					$keys[] = 'journal';
					$values[] = '"' . str_replace('"', '""', $v) . '"';					
				}
				break;

			case 'title':
				// handle case where it might be an array
				
				$title = $v;
				if (is_array($title))
				{
					if (count($title) > 0)
					{
						$title = $title[0];
					}
					else
					{
						$title = '';
					}
				}
				
				// clean
				$title = preg_replace('/\R/u', ' ', $title);
				$title = preg_replace('/\s\s+/u', ' ', $title);
				
				if ($title != '')
				{
					$keys[] = 'title';
					$values[] = '"' . str_replace('"', '""', $title) . '"';	
				}
				break;
				
			case 'multi':
				if (isset($v->_key))
				{
					foreach ($v->_key as $mlkey => $mlvalues)
					{
						switch ($mlkey)
						{
							case 'title':
								foreach ($mlvalues as $language => $language_string)
								{
									$kk = array();
									$vv = array();
									$kk[] = "`key`";
									$vv[] = '"title"';

									$kk[] = 'language';
									$vv[] = '"' . $language . '"';
			
									$kk[] = 'value';
									$vv[] = '"' . str_replace('"', '""', $language_string) . '"';	

									$multilingual_keys[] = $kk;
									$multilingual_values[] = $vv;								
								}
								break;
						
						
							default:
								break;
						}
					}
				}
				break;

			case 'ISSN':
				if (is_array($v))
				{
					$keys[] = 'issn';
					
					$issn = $v[0];
					
					//JaLC
					if (strlen($issn) == 8)
					{
						$issn = substr($issn, 0, 4) . '-' . substr($issn, 4);
					}
					
					$values[] = '"' . str_replace('"', '""', $issn) . '"';					
				}
				else 
				{
					$keys[] = 'issn';
					$values[] = '"' . str_replace('"', '""', $v) . '"';					
				}
				break;
				
			case 'ISBN':
				$keys[] ='isbn';
				$values[] = '"' . $v[0] . '"';	
				break;										
	
			case 'issued':
				$keys[] = 'year';
				$values[] = '"' . $v->{'date-parts'}[0][0] . '"';		
				
				$date = array();
				
				if (count($v->{'date-parts'}[0]) > 0) $date[] = $v->{'date-parts'}[0][0];
				if (count($v->{'date-parts'}[0]) > 1) $date[] = str_pad($v->{'date-parts'}[0][1], 2, '0', STR_PAD_LEFT);
				if (count($v->{'date-parts'}[0]) > 2) $date[] = str_pad($v->{'date-parts'}[0][2], 2, '0', STR_PAD_LEFT);

				if (count($date) == 1)
				{
					$date[] = '00';
					$date[] = '00';
				}

				if (count($date) == 2)
				{
					$date[] = '00';
				}
								
				if (count($date) == 3)
				{
					$keys[] = 'date';
					$values[] = '"' . join('-', $date) . '"';						
				}
							
				break;
	
			case 'page':
				// Airiti journals sometimes have extra (-\d+) if each article is paginated 1-n
				if (preg_match('/(?<spage>S?\d+)-(?<epage>S?\d+(-\d+)?)/', $v, $m)) 
				{
					$keys[] = 'spage';
					$values[] = '"' . $m['spage'] . '"';					

					$keys[] = 'epage';
					$values[] = '"' . $m['epage'] . '"';					

				}
				else
				{
					$keys[] = 'spage';
					$values[] = '"' . $v . '"';					
		
				}
				break;
			
			case 'article-number':
				$keys[] = 'article_number';
				$values[] = '"' . $v . '"';							
				break;
				
			case 'author':
				$authors = array();
				
				foreach ($v as $author)
				{
					if (isset($author->family))
					{
						$name = $author->family;
						if (isset($author->given))
						{
							$name = $author->given . ' ' . $name;
						}					
						$authors[] = $name;
					}
					else
					{
						if (isset($author->literal))
						{
							$authors[] = $author->literal;
						}
					}
				}
				
				if (count($authors) > 0)
				{
					$keys[] = 'authors';
					$values[] = '"' . str_replace('"', '""', join(';', $authors)) . '"';	
				}
				break;
		
			case 'link':
				foreach ($v as $link)
				{
					if (($link->{'content-type'} == 'application/pdf') && ($pdf == ''))
					{					
						$pdf = $link->URL;	
					}
					
					// JALC typically has content-type = "unspecified"
					if (preg_match('/_pdf$/', $link->URL) && ($pdf == ''))
					{
						$pdf = $link->URL;	
					}
					
					// XML
					if ($link->{'content-type'} == 'application/xml')
					{					
						$keys[] = 'xml';
						$values[] = '"' . $link->URL . '"';							
					}
					
					
				}					
				break;
				
				// some have PDF as a primary resource, e.g. 10.18500/1814-6090-2022-22-3-4-147-157
			case 'resource':
				if (isset($v->primary))
				{
					if (isset($v->primary->URL))
					{
						if (preg_match('/\.pdf$/', $v->primary->URL))
						{
							$pdf = $v->primary->URL;	
						}
					}				
				}				
				break;
				
							
			default:
				break;
		}
	}
	
	if ($pdf != '')
	{
		$keys[] = 'pdf';
		$values[] = '"' . $pdf . '"';		
	}
	
	// store object itself as JSON
	$json = json_encode($csl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	$json = str_replace("'", "''", $json);

	$keys[] = 'json';
	$values[] = "'" . $json . "'" ;
	
	$sql = '';
	
	
	if (1)
	{	
		$sql = 'REPLACE INTO ' . $table . '(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";
	}
	else
	{
		$sql = 'INSERT OR IGNORE INTO ' . $table . '(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";
	}
	
	$n = count($multilingual_keys);
	for($i =0; $i < $n; $i++)
	{
		$multilingual_keys[$i][] = 'guid';
		$multilingual_values[$i][] = '"' . $guid . '"';

		if (1)
		{
			$sql .= 'REPLACE INTO multilingual(' . join(',', $multilingual_keys[$i]) . ') values('
				. join(',', $multilingual_values[$i]) . ');' . "\n";		
		}
		else
		{
			$sql .= 'INSERT OR IGNORE INTO multilingual(' . join(',', $multilingual_keys[$i]) . ') values('
				. join(',', $multilingual_values[$i]) . ');' . "\n";
		}
	}
	
	return $sql;
}

//----------------------------------------------------------------------------------------
// test
if (0)
{
	$json = '{
        "type": "article-journal",
        "author": [
            {
                "family": "Adelung",
                "given": "N."
            }
        ],
        "title": "Contributions a la connaissance des Blattaires",
        "container-title": "Annuaire du Musée zoologique de l\'Academie des Sciences",
        "volume": "21",
        "page": "243-268",
        "issued": {
            "date-parts": [
                [
                    1917
                ]
            ]
        }
    }';
    
    $obj = json_decode($json);
    
    print_r($obj);
    
    $ris = csl_to_ris($obj);
    
    //echo $ris;
    
    $openurl = csl_to_openurl($obj);
    
    //echo $openurl . "\n";
    
 	$tsv = csl_to_tsv($obj);
    
    echo $tsv . "\n";    

	$sql = csl_to_sql($obj);
    
    echo $sql . "\n";    

}


?>
