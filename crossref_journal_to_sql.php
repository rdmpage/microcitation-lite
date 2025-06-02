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

$id = 174525; // Tropical Zoology

$id = 249071; // Caldasia

$id = 214148; // Arquivos de Zoologia

$id = 60527; // Journal of Medical Entomology

$id = 144145; // Asian Herpetological Research

$id = 309444; // Japanese Journal of Herpetology

$id = 79098; // Israel Journal of Ecology and Evolution

$id = 81082; // Malacologia

$id = 57030; // 

$id = 330075; // Zoosystematica Rossica


$id =  525223; // Bulletin of the Florida Museum of Natural History

$id =  325270; // Entomologicheskoe Obozrenie

$id =  10137; // Feddes Repertorium
//$id =  66409; // Feddes Repertorium specierum novarum regni vegetabilis Supplements
//$id =  66464; // Repertorium novarum specierum regni vegetabilis"
//$id =  460271; // Repertorium novarum specierum regni vegetabilis"

$id =  65431; // Journal of the Torrey Botanical Society

$id =  283647; // 

$id =  215988; // Mycobiota

$id =  92546; // Fungal Diversity

$id =  297283; // Proceedings of the Royal Society of Queensland

$id =  62569; // Madroño

$id =  303718;

$id =  326656; // Rev. Colomb. Entomol.

$id =  459128;
$id =   92245;
$id =   55373;

$id =   201365;
$id =   222666; // Botanical Studies
$id = 	550374; // Species

$id = 	593425; // Adansonia

/*
"Bulletin du Muséum national d histoire naturelle","601045","Biodiversity Heritage Library","03009386","","","","(1971)[10]10[11]11[12]12[13]13[14]14[15]15[16]16[1]1[2]2[3]3[4]4[5]5[6]6[7]7[8]8[9]9(1972)[102]76[103]77[23]17[24]18[25]19[26]20[27]21[28]22[29]23[30]24[31]25[32]26[33]27[34]28[35]29[36]30[37]31[38]32[39]33[40]34[41]35[42]36[43]37[44]38[53]39[54]40[55]41[56]42[57]43[58]44[59]45[60]46[61]47[62]48[63]49[64]50[65]51[66]52[67]53[68]54[69]55[70]56[78]57[79]58[80]59[81]60[82]61[83]62[84]63[85]64[86]65[87]66[88]67[89]68[90]69[91]70[92]71[93]72[94]73[95]74[96]75(1973)[104]78[105]79[106]80[107]81[108]82[109]83[110]84[111]85[112]86[113]87[114]88[115]89[116]90[117]91[120]92[121]93[122]94[123]95[124]96[125]97[134]98[135]99[136]100[137]101[138]102[139]103[140]104[141]105[142]106[143]107[144]108[145]109[146]110[166]111[167]112[168]113[169]114[170]115[171]116[178]117[179]118[180]119[181]120[182]121[183]122[184]123[185]124[186]125[187]126[188]127[196]128[197]129[198]130[199]131(1974)[202]132[203]133[204]134[205]135[206]136[207]137[208]138[209]139[210]140[211]141[212]142[213]143[216]144[217]145[218]146[219]147[220]148[221]149[222]150[223]151[224]152[225]153[226]154[231]155[232]156[233]157[234]158[235]159[236]160[237]161[238]162[239]163[240]164[241]165[242]166[243]167[244]168[245]169[246]170[247]171[248]172[251]173[252]174[253]175[254]176[255]177[256]178[257]179[258]180[259]181[260]182[261]183[262]184[263]185[264]186[265]187[266]188[267]189[268]190(1975)[281]191[282]192[283]193[284]194[285]195[286]196[287]197[288]198[289]199[290]200[291]201[292]202[293]203[294]204[295]205[296]206[297]207[298]208[299]209[300]210[301]211[302]212[303]213[304]214[305]215[306]216[307]217[311]218[312]219[313]220[314]221[315]222[316]223[317]224[318]225[319]226[320]227[321]228[322]229[323]230[324]231[330]232[331]233[332]234[333]235[334]236[335]237[336]238[337]239[338]240[339]241[340]242(1976)[350]243[351]244[352]245[353]246[354]247[355]248[356]249[357]250[358]251[359]252[360]253[361]254[362]255[368]256[369]257[370]258[371]259[372]260[373]261[374]262[375]263[376]264[377]265[378]266[379]267[380]268[387]269[388]270[389]271[390]272[391]273[392]274[393]275[394]276[400]277[401]278[402]279[403]280[404]281[405]282[406]283[407]284[408]285[409]286[410]287[411]288[412]289[413]290[414]291[415]292[417]294(1977)[416]293[425]295[426]296[427]297[428]298[429]299[430]300[431]301[432]302[433]303[434]304[435]305[436]306[444]307[445]308[446]309[447]310[448]311[449]312[450]313[451]314[452]315[453]316[454]317[455]318[456]319[457]320[458]321[459]322[466]323[467]324[468]325[469]326[470]327[471]328[472]329[473]330[474]331[475]332[476]333[477]334[478]335[479]336[480]337[481]338[482]339[491]340[493]342[494]343[495]344[496]345[497]346[498]347[499]348[500]349[501]350(1978)[492]341[510]351[513]352[514]353[515]354[517]355[520]356"
"Bulletin du Muséum national d histoire naturelle","525743","Biodiversity Heritage Library","01810626","","","","""(1979)[1]1,2 supplément,2,3,4(1980)[2]1,2(1981)[3]1,2,3,4(1982)[4]1,3(1983)[5]1,2,3,4(1984)[6]1,2,3,4(1985)[7]1,2,3,4(1986)[8]1,2,3,4(1987)[9]1,2,3,4(1988)[10]1,2,3,4(1989)[11]1,2,3,4(1990)[12]1,1 supplement,2,3(1991)[13]1(1992)[14]1,2,3(1995)[17]1,3(1996)[18]1,3"""
"Bulletin du Muséum National d Histoire Naturelle Section B Botanique biologie et écologie végétales phytochimie","508803","Biodiversity Heritage Library","01810634","","","","""(1979)[1]1,2,3,4(1980)[2]1,2,3,4"""
"Bulletin du Muséum national d histoire naturelle","297260","Biodiversity Heritage Library","11488425","00274070","","","(1897)[1897][3](1898)[1898][3][4](1899)[1899][5](1900)[1900][6](1901)[56][6][7](1903)[1903][9](1904)[9](1906)[12][1906](1907)[13][1907](1908)[14][1907][1908](1909)[14][1908](1910)[16][1910](1911)[1910](1913)[1913][19](1914)[1913](1916)[1916][22](1917)[1916](1918)[1918][24](1919)[1919][25](1920)[1920][26](1921)[1919][1921][26][27]"
*/

$id = 	297260; // Bulletin du Muséum national d histoire naturelle ISSN 0027-4070

$id = 	285646;

$id = 	525743; // Bulletin du Muséum national d histoire naturelle","525743","Biodiversity Heritage Library","01810626"


$id = 	284727; // Revue suisse de zoologie

$id = 	308998;
$id = 	295297;
$id = 	305838;

$id = 	458410; // The Festivus

$id = 	125165;

$id =   424018; // Boletim do Museu Paraense Emílio Goeldi Nova Série Botânica
$id =   392343; // Boletim do Museu Paraense Emílio Goeldi - Ciências Naturais

$id =     5956; // Australasian Plant Pathology
$id =   462434; // Journal of Tropical Coleopterology

$id =    16437; // Archiv für Hydrobiologie

$id =   302770; // Annales de la Société linnéenne de Lyon

$id =   559458; // Journal of Conchology

$id =   310022;

$id =   83871; // Transactions of the Linnean Society of London 2nd Series Zoology
$id =   83868; // Transactions of the Linnean Society of London 3rd Series

$id =   413497;
$id =   251726;

$id =   297275;

$id =   551750;
$id =   224727;
$id =   223026; // "Bollettino della Società Entomologica Italiana","223026"

$id =   57434; // Journal of Arachnology

$id =   297275;
$id =    81083;


$id =    55683;
//$id =    66464;
//$id =   330699;

$id =   307174;
$id =   266071;

$id =   423060; // Arthropod Systematics & Phylogeny
$id =   476223; // SHILAP
$id =    70538; // Bulletin Southern California Academy of Sciences


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
