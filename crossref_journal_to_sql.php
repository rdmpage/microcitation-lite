<?php

// Import DOIs as SQL via the CrossRef journal id (which you can get from Wikidata)

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
	  
	  CURLOPT_HEADER 		=> FALSE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	  CURLOPT_COOKIEJAR=> sys_get_temp_dir() . '/cookies.txt',
	  CURLOPT_COOKIEFILE=> sys_get_temp_dir() . '/cookies.txt',
	  
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

//--------------------------------------------------------------------------
function get_redirect($url)
{	
	$redirect = '';
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => FALSE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	  CURLOPT_HEADER => TRUE,
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
		 
	$header = substr($data, 0, $info['header_size']);
	
	$http_code = $info['http_code'];
	
	if ($http_code == 303)
	{
		$redirect = $info['redirect_url'];
	}
	
	if ($http_code == 302)
	{
		$redirect = $info['redirect_url'];
	}

	return $redirect;
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

//----------------------------------------------------------------------------------------


$id = 2284; 	// Cryptogamie Mycologie
$id = 542923; 	// Nuytsia

$id = 422565;
$id = 30344;

$id = 5533; 	// Nova Hedwigia
$id = 54210; 	// South African Journal of Botany
$id = 149285; 	// Mycosphere
$id = 144625; 	// Mycokeys
$id = 315451;	// Plant and Fungal Systematics
$id = 148585;	// European Journal of Taxonomy
$id = 204705; 	// Microorganisms
$id = 311717; 	// Fungal Systematics and Evolution
$id = 197285; 	// Phytotaxa
$id = 79164; 	// Anales del Jardín Botánico de Madrid
$id = 72459;	// Persoonia
$id = 55383;	// American Journal of Botany
$id = 53998;	// Mycological Progress
$id = 125406;	// IMA Fungus
$id = 30563;	// Microbiological Research

$id = 5533; 	// Nova Hedwigia
$id = 311495; 	// Karstenia
$id = 55679;	// The Bryologist
$id = 5787;		// Amphibia-Reptilia
$id = 62933;	// Bulletin du Jardin botanique de l'État a Bruxelles
$id = 71519;	// Canadian Journal of Botany
$id = 72459;	// Persoonia
$id = 245667;	// Revista Peruana de Biologia 
$id = 72421;	// Notizblatt des Königl. Botanischen Gartens und Museums zu Berlin 
$id = 107345; 	// Proceedings of the Zoological Society of London
$id = 314924;	// Bonplandia
$id = 306411;	// Acta Botanica Malacitana
$id = 469482; 	// Muelleria
$id = 55482; 	// Bulletin of the Torrey Botanical Club
$id = 315165;	// Records of the Zoological Survey of India
//$id = 297259;	// Records of the Indian Museum
$id = 71321;	// Bulletin of the Peabody Museum of Natural History
$id = 305963;	// Flora oder Allgemeine Botanische Zeitung
$id = 105786;	// Proceedings of the Royal Entomological Society of London. Series B, Taxonomy
$id = 126005;	// Smithsonian Contributions to Botany
$id = 379736;	// Ecologica Montenegrina
$id = 73440;	// Archives of Biological Sciences
$id = 6032;		// Edinburgh Journal of Botany
$id = 465517; 	// Metamorphosis
$id = 366717;	// Amurian Zoological Journal

$id = 85709;	// Ceylon Journal of Science
$id = 300655;	// Ceylon Journal of Science
$id = 196026;	// Cunninghamia
$id = 476223;	// SHILAP Revista de lepidopterología
$id = 265508; 	// Fragmenta Entomologica (no longer with CrossRef)
$id = 300777; 	// Acarina

$id = 306560; 	// Zoological Research 2017-onwards
$id = 79061;	// Zoological Research

$id = 87512;	// Gayana Botánica
$id = 82626;	// Journal of Systematics and Evolution

$id = 76599; 	// ZooKeys - too big, need to filter figures, etc :O

$id = 251369;	// Arctoa

$id = 2336;		// Cryptogamie Bryologie

$id = 423343;	// Zoo Indonesia
$id = 45763;	// Revista do Instituto de Medicina Tropical de São Paulo
$id = 45699;	// Papéis Avulsos de Zoologia

$id = 327691;	// Herpetological Journal
$id = 85445;	// Philippine Journal of Systematic Biology
//$id = 367377;	// Philippine Journal of Systematic Biology

$id = 216386;	// Annales de la Société entomologique de France

$id = 60657;	// Breviora

$id = 421280;	// Vertebrate Zoology

$id = 298840;	// Gardens' Bulletin Singapore

$id = 46100;	// Journal of Molluscan Studies

$id = 85393; // Transactions of the British Mycological Society

$id = 235848; // European Journal of Entomology

$id = 213505; // Therya

$id = 109985; // Journal of the Linnean Society of London, Zoology

$id = 297253; // The Bulletin of zoological nomenclature

$id = 55467; // Taxon

$id = 376658; // Russian Journal of Herpetology

$id = 3798; // Ibis

$id = 546247; // Bulletin de la Société des naturalistes luxembourgeois

$id = 326162; // Proceedings of the Zoological Institute RAS

$id = 195665; // Medical Entomology and Zoology 

$url = 'http://data.crossref.org/depositorreport?pubid=J' . $id;

$text = get($url);

$rows = explode("\n", $text);

//print_r($rows);

$dois=array();

foreach ($rows as $row)
{
	if (preg_match('/^(?<doi>10\.\d+[^\s]+)\s/', $row, $m))
	{
		$dois[] = $m['doi'];
	}
}

$count = 1;

foreach ($dois as $doi)
{
	// DOI prefix
	$parts = explode('/', $doi);
	$prefix = $parts[0];
	
	// Agency lookup
	$agency = doi_to_agency($prefix_to_agency, $prefix, $doi);
	
	$doi = strtolower($doi);

	$url = 'https://doi.org/' . $doi;	
	$json = get($url, 'application/vnd.citationstyles.csl+json');
	$obj = json_decode($json);
	
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
