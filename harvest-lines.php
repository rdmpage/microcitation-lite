<?php

// Given a set of lines with reference information, such as table of contents, parse it
// and export as TSV. Also features option to show the results on screen as a table for
// ease of debugging.

// 1
if (0)
{
$lines=array(
'1. Araliaceae, novae, adiecta etc., auct. F. A. G. MIQUEL, p. 1--27, et mantissa p. 219-220.',
'2. Ericaceae iaponicae; auct. eodem, p. 28--35.',
'3. Ericaceae Archipelagi indici; auct. codem, p. 36--45, et mantissa p. 220 --221.',
'4. Filices, praesertim indicae et iaponicae, auct. G. MetTENIO, pars prima, p. 46--58.cont. p. 222.',
'5. Equisetaceae, auct. I. MiLDE, p. 59--71, et contin. p. 212.',
'6. Ampelideae novae, adiecta etc.; auct. F. A. G. MIQUEI, p. 72--101.',
'7. Adnotationes de Cupuliferis, auct. codem, p. 102-121, et mant. p. 221.',
'8. Araceae, pars prior, nova genera et species, auct. H. W. ScHort, p. 122--131; mantissa p. 221; contin. p. 278.',
'9. Thymelaeacearum genera nova, auct. F. A. G. MrQUBI, p. 132--133.',
'10. Piperaceae, auct. codem, p. 134-141.',
'11. Polygalaceae, praesertim indicae, auct. I. C. HasSKaRL, p. 142--196.',
'12. Animadversiones in nonnullas Bignoniaceas, auct. F. A. G. MiQuet, p. 197--202.',
'13. Poikilospermum, gen, nov. Urlicacearum, auct. codem, p. 203.',
'14. Heliciae species amboinenses, auct. eodem, p. 204.',
'15. Myristiceae a TexsMaNNo et DE VRIESE collectae, auct. codem, p. 205--207.',
'16. Observationes de Clusiaceis, auch, eodem, p. 208--209.',
'17. Scaevolae species moluccanae, auct. eodem, p. 210.',
'18. Aurantiacene novae, auct. codem, p. 211.',
'19. Pygei species novae, auct. codem, p. 212.',
'20. Dipterocarpeae novae vel minus cognitae, auct. codem, p. 213 -215.',
'21. Melastomaceae, a TaysmanNo et DE VRIBSE lectae, auct. eodem, p. 216 - 217.',
'22. Antidesmeae novae, auct. eodem, p. 218.',
'23. Mantissa Araliacearum, Ericacearum, Cupuliferarum et Aracearum, auct. eodem, p. 219--221.',
'24. Filices, praesertim indicae et isponicae, anel. G. MarTENto, pars allera, p. 222-241.',
'25. Equisetaceae, addenda, asel. I. Mr.De, p. 242-247.',
'86. Pomaceae, Acerineae et Berberideae, auel. C. Koc, p. 248--253.',
'27. Cinnamomi generis revisio, anet. F. A. G. MrQUsi, p. 254--270.',
'28. Xanthophylli species, auel. eoden, p. 271--277.',
'29. Araceae, auch. H. W. Schott: pars allera, nova genera et species, adiect. obsero, p. 278 -285; el adrol. editoris, p. 285-286.',
'30. Hepaticae lungermannicac Arch. ind. et Taponias, auch. C. M. vAN DER SaNDE LACOsTE, p. 287--314.',
'31. Wormia revoluta, auctore P. A. G. MrQuat, p. 315.',
'32. De Orchipeda, auel. eodem, p. 316--317.',
'38. Adnotatio ad Cinnamomi revisionem, p. 317.',
'34. Xanthophylli species addendae, p. 317.',
'35. Addenda et Corrigenda, p. 318.',
'36. Emendandum, p. 318.',
'Index, p. 319-331.',
);
}

// 4
if (1)
{
$lines=array(
'1. Monographia Meliacearum Archipelagi indici, auctore F. A. GUIL. MIQUEL, p. 1--61.',
'2. Ranunculaceae, Magnoliaceae, Dilleniaceae et Menispermeae Archipelagi indici, anctore F. A. Guit. MIQUEt., p. 65-88.',
'3. Teysmannia, Palmarum genus, descripsit F. A. GUIL. MIQUEL, p. 89-90.',
'4. Filices, auctore F. A. GuIt. MIQUEL, p. 91--98.',
'5. Observationes de Zingiberaceis, auctore F. A. GUIL. MiQUst, p. 99--102.',
'6. Adnotationes de Ternstroemiaceis, auclore F. A. GUIL. MIQUEt, p. 103--114.',
'7. Combretum arboreum, descripsit F. A. GuIl. MiQUEt, p. 115.',
'8. De quibusdam Burseraceis et Anacardiaceis, auctore F. A. GUIL. MIQUEL, p. 116-118.',
'9. Observationes de quibusdam Euphorbiaceis Archipelagi indici, auctore R. H. C. C. ScHEPFER, p. 119--127.',
'10. De quibasdam Rubiaceis, Apoeyneis et Asclepiadeis, scripsit F. A. GUIt. MIQUEL, p. 128--142.',
'11. Primulacene Archipelagi indici, adiectis observationibns de iaponicis, auctore F. A. GULL. MiQUEL, p. 143-147.',
'12. Hippocrateaceae Archipelagi indici, recensuit F. A. GulL. MIQUeL, p. 148--154.',
'13. Filices, auctore F. A. GUIl. MIQUEL, p. 155-169 (cf. p. 91).',
'14. Filices, auctore G. MeTTENIos, p. 170-174.',
'15. Hypoxidene indicae, recensuit S. KURz, p. 175--178.',
'16. Ecloge Rubiacearum Archipelagi indici, auctore F. A. GUll. MIQUEL. - Pars prima n. 179-213.',
'17. Violacearum guarundam recensio, auctore F. A. GUIl.. MiQUEL, p. 214-218.',
'18. De Grammatophyllo Rumphiano, scripsit F. A. GUll. MIQUEL, p. 219- 220.',
'19. Ecloge Rabiacearum Archipelagi indici, auctore F. A. GUIL. MIQUEL. - Pars altera p. 221--262.',
'20. De Cinchonae speciebus quibusdam, adiectis is quae in Iava coluntur, scripsit F. A. GUIL. MIQUEL, p. 263-275.',
'21. Filices, auclore MaxImILIANo KUHN, p. 276-300.',
'22. Observation es de Urticeis quibusdam et de Patoua, scripsit F. A. GuiL. MIQUEL, p. 301-307.',
'Addenda et Corrigenda, p. 308.',
);
}

// 3
if (0)
{
$lines=array(
'1. Prolusio Florae Iaponicae, autore F. A. G. MIQUBL, p. 1--66.',
'2. Violaceae, auctore C. A. J. A. OUDemans, p. 67--78.',
'3. Mantissa Aroidearam indicarum, cum Catalogo omnium specierum in Archipelago detectarum, autore F. A. G. MIQUeL, p. 79-82.',
'4. Annotationes de Dipterocarpeis, cum Catalogo omnium in Archipelago indico detectarum, auclore F. A. G. MIQUEL, p. 88-85.',
'5. Observationes de generibus quibusdam indicis (Sindora, Acrocarpus, Pyrospermui, Nothoenestis, Troostwykia, Mildea, Lunasia, Inodaphnis, Nothoprotium, Calyptroon), autore F. A. G. MrQust, p. 86--90.',
'6. Prolusio Florae Iaponicae, auctore F. A. G. MiQUEL, p. 91-209.',
'7. Artocarpeae, auctore F. A. G. MiQUet, p. 210-235.',
'8. Chrysobalaneae quaedam indicae, auctore F. A. G. MIQUeL, p. 236--287.',
'9. Eriocaulacese, auctore Fr. KöRNICKE, p. 238--241.',
'10. Rutacearum quarundam illustratio, auctore F. A. G. MIQUEL, p. 242- 246.',
'11. Annotationes de Phytoerenes speciebus Archipelagi indici, auctore F. A. G. MIQURI, p. 247-248.',
'12. De Nyctocalo et Radermachera, generibus Bignoniacearum, autore F. A. G. MIQUEL, p. 249-250.',
'13. De Clerodendri quibusdam speciebus, auctore F. A. G. MIQUBL, p. 251--254.',
'14. Coniferae Iunghuhnianae posteriores, enumeravit P. DE BoER, p. 255.',
'15. Algarum iaponicarum Musei Lugduno-Batavi index precursorius, auctore W. F. R. SURINGAR, p. 256- 259.',
'16. Annotationes de Ficus speciebus, auclore F. A. G. MrQUEt, p. 260--284.',
'17. Ficuum gerontogaearum hactenus cognitarum enumeratio systematica, adiectis synonymis, auctore F. A. G. MIQUEL, p. 285-297.',
'18. Appendix sistens enumerationem Ficuum Novi Orbis, p. 297--300.',
'Index, p. 301-314.'
);
}

// 2
if (0)
{
$lines=array(
'1. Anonaceae Archipelagi indici, exposuit F. A. G. MiQUEt, p. 1--45.',
'2. Myristicene, auctore F. A. G. MiQUet, p. 46- 51.',
'3. Observationes de quibusdam Pandaneis, in horto bogoriensi lavae cultis, auelore S. Kunz, p. 52-51.',
'4. Polygonacene, avetore C. F. MetssNeR, p. 55- 65.',
'5. Legnotidene Archipelagi indici, esposi! F. A. G. Mrquet, p. 66--67.',
'6. Phoenicosperma, Tiliacearum genus, proposuit P. A. G. Mrquei, p. 68.',
'7. Prolusio Florae Iaponicae, auctore E. A. G. MiQuEt, p. 69-212.',
'8. Illigerae species Archipelagi indici, exposuit F. A. G. MiQUaI, p. 213--215.',
'9. Fagraese species in Archipelago indico et Nova Guinea hactenus detectae, recensuil F. A. G. MIQUEL, p. 216-218.',
'10. Filices, praesertim indicae et iaponicae, auctore G. MetTeNIUs, p. 219-240.',
'11. Nymphaeaceae, erposnit R. CasPARY, p. 241--256.',
'12. Prolusio Flore iaponicae, auclore P. A. G. MiQUeI, p. 257-260.',
'Index, p. 301--313.',
);
}

//----------------------------------------------------------------------------------------

$mode = 0; // human-readable display
$mode = 1;	// TSV

// store parsed references
$references = array();

// array of keys and the size needed to display contents
$sizes = array(
	'id'	=> 4,
	'title' => 60,
	'author' => 40,
	'journal' => 40,
	'volume' => 4,
	'spage'	=> 4,
	'epage' => 4
);

// things that failed
$failed = array();


$journal = 'Annales Musei botanici lugduno-batavi';
$volume = 4;

foreach ($lines as $line)
{
	echo $line . "\n";

	$matched = false;
	
	if (!$matched)
	{	
		if (preg_match('/^(?<id>\d+\.\s+)?(?<title>.*)[,|;]\s+(auel\. eodem|anctore|recensuit|(de)?scripsit|enumeravit|autore|auelore|recensuil|avetore|auct.|auc[l|t]ore|auch[.|,]|a[u|n|s]e[l|t]\.|proposuit|esposi!|erposnit|exposuit)\s+(?<author>.*),\s+p\.\s+(?<spage>\d+)(\s*(-|--)\s*(?<epage>\d+))?/u', $line, $m))
		{
			print_r($m);
			
			$m['journal'] = $journal;
			$m['volume'] = $volume;
		
			$references[] = $m;
		
			$matched = true;
		}
	}
	
	
	if (!$matched)
	{	
		if (preg_match('/^(?<id>\d+\.\s+)?(?<title>.*),(\s+p\.)?\s+(?<spage>\d+)(\s*(-|--)\s*(?<epage>\d+))?/u', $line, $m))
		{
			print_r($m);
			
			$m['journal'] = $journal;
			$m['volume'] = $volume;
			
		
			$references[] = $m;
		
			$matched = true;
		}
	}
	
	if (!$matched)
	{	
		$failed[] = $line;
	}	
	

}

// display so we can see any problems
if ($mode == 0)
{
	$pad = true;
	$delimiter = " | ";
}

// TSV
if ($mode == 1)
{
	$pad = false;
	$delimiter = "\t";

}

foreach ($references as $reference)
{
	//print_r($reference);
	
	$terms = array();
	foreach ($sizes as $k => $v)
	{
		if (isset($reference[$k]))
		{
			if ($pad)
			{
				$terms[] = str_pad(substr($reference[$k], 0, $v), $v, ' '); 
			}
			else
			{
				$terms[] = trim($reference[$k]);
			}
		}
		else
		{
			if ($pad)
			{
				$terms[] = str_pad("", $v, ' '); 
			}
			else
			{
				$terms[] = "";
			}
			
		}
	}
	
	echo join($delimiter, $terms) . "\n";
}

print_r($failed);



?>