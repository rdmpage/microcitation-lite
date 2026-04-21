<?php

// Check URls


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
	
	return $info['http_code'];
}




$urls=array(

'https://soil-organisms.org/index.php/SO/article/download/43/29',
'https://soil-organisms.org/index.php/SO/article/download/45/28',
'https://soil-organisms.org/index.php/SO/article/download/47/25',
'https://soil-organisms.org/index.php/SO/article/download/48/27',
'https://soil-organisms.org/index.php/SO/article/download/74/51',
'https://soil-organisms.org/index.php/SO/article/download/75/52',
'https://soil-organisms.org/index.php/SO/article/download/76/53',
'https://soil-organisms.org/index.php/SO/article/download/78/54',
'https://soil-organisms.org/index.php/SO/article/download/79/55',
'https://soil-organisms.org/index.php/SO/article/download/80/56',
'https://soil-organisms.org/index.php/SO/article/download/81/59',
'https://soil-organisms.org/index.php/SO/article/download/82/57',
'https://soil-organisms.org/index.php/SO/article/download/83/60',
'https://soil-organisms.org/index.php/SO/article/download/85/62',
'https://soil-organisms.org/index.php/SO/article/download/86/63',
'https://soil-organisms.org/index.php/SO/article/download/87/64',
'https://soil-organisms.org/index.php/SO/article/download/88/65',
'https://soil-organisms.org/index.php/SO/article/download/89/66',
'https://soil-organisms.org/index.php/SO/article/download/90/67',
'https://soil-organisms.org/index.php/SO/article/download/91/68',
'https://soil-organisms.org/index.php/SO/article/download/92/69',
'https://soil-organisms.org/index.php/SO/article/download/95/71',
'https://soil-organisms.org/index.php/SO/article/download/96/70',
'https://soil-organisms.org/index.php/SO/article/download/97/72',
'https://soil-organisms.org/index.php/SO/article/download/98/73',
'https://soil-organisms.org/index.php/SO/article/download/99/74',
'https://soil-organisms.org/index.php/SO/article/download/100/75',
'https://soil-organisms.org/index.php/SO/article/download/101/76',
'https://soil-organisms.org/index.php/SO/article/download/102/77',
'https://soil-organisms.org/index.php/SO/article/download/103/79',
'https://soil-organisms.org/index.php/SO/article/download/104/80',
'https://soil-organisms.org/index.php/SO/article/download/105/81',
'https://soil-organisms.org/index.php/SO/article/download/106/82',
'https://soil-organisms.org/index.php/SO/article/download/294/284',
'https://soil-organisms.org/index.php/SO/article/download/295/285',
'https://soil-organisms.org/index.php/SO/article/download/49/31',
'https://soil-organisms.org/index.php/SO/article/download/50/30',
'https://soil-organisms.org/index.php/SO/article/download/52/32',
'https://soil-organisms.org/index.php/SO/article/download/275/265',
'https://soil-organisms.org/index.php/SO/article/download/271/261',
'https://soil-organisms.org/index.php/SO/article/download/272/262',
'https://soil-organisms.org/index.php/SO/article/download/273/263',
'https://soil-organisms.org/index.php/SO/article/download/274/264',
'https://soil-organisms.org/index.php/SO/article/download/276/266',
'https://soil-organisms.org/index.php/SO/article/download/277/267',
'https://soil-organisms.org/index.php/SO/article/download/278/268',
'https://soil-organisms.org/index.php/SO/article/download/279/269',
'https://soil-organisms.org/index.php/SO/article/download/270/335',
'https://soil-organisms.org/index.php/SO/article/download/253/241',
'https://soil-organisms.org/index.php/SO/article/download/254/242',
'https://soil-organisms.org/index.php/SO/article/download/255/243',
'https://soil-organisms.org/index.php/SO/article/download/256/246',
'https://soil-organisms.org/index.php/SO/article/download/257/247',
'https://soil-organisms.org/index.php/SO/article/download/258/248',
'https://soil-organisms.org/index.php/SO/article/download/259/249',
'https://soil-organisms.org/index.php/SO/article/download/260/250',
'https://soil-organisms.org/index.php/SO/article/download/261/252',
'https://soil-organisms.org/index.php/SO/article/download/262/253',
'https://soil-organisms.org/index.php/SO/article/download/263/254',
'https://soil-organisms.org/index.php/SO/article/download/264/255',
'https://soil-organisms.org/index.php/SO/article/download/265/256',
'https://soil-organisms.org/index.php/SO/article/download/266/257',
'https://soil-organisms.org/index.php/SO/article/download/267/258',
'https://soil-organisms.org/index.php/SO/article/download/269/259',
'https://soil-organisms.org/index.php/SO/article/download/244/233',
'https://soil-organisms.org/index.php/SO/article/download/245/234',
'https://soil-organisms.org/index.php/SO/article/download/246/235',
'https://soil-organisms.org/index.php/SO/article/download/248/236',
'https://soil-organisms.org/index.php/SO/article/download/249/237',
'https://soil-organisms.org/index.php/SO/article/download/250/238',
'https://soil-organisms.org/index.php/SO/article/download/251/239',
'https://soil-organisms.org/index.php/SO/article/download/252/240',
'https://soil-organisms.org/index.php/SO/article/download/10/9',
'https://soil-organisms.org/index.php/SO/article/download/14/13',
'https://soil-organisms.org/index.php/SO/article/download/15/14',
'https://soil-organisms.org/index.php/SO/article/download/16/15',
'https://soil-organisms.org/index.php/SO/article/download/17/16',
'https://soil-organisms.org/index.php/SO/article/download/18/17',
'https://soil-organisms.org/index.php/SO/article/download/20/18',
'https://soil-organisms.org/index.php/SO/article/download/21/19',
'https://soil-organisms.org/index.php/SO/article/download/22/20',
'https://soil-organisms.org/index.php/SO/article/download/23/21',
'https://soil-organisms.org/index.php/SO/article/download/232/225',
'https://soil-organisms.org/index.php/SO/article/download/233/224',
'https://soil-organisms.org/index.php/SO/article/download/234/226',
'https://soil-organisms.org/index.php/SO/article/download/235/227',
'https://soil-organisms.org/index.php/SO/article/download/236/228',
'https://soil-organisms.org/index.php/SO/article/download/237/229',
'https://soil-organisms.org/index.php/SO/article/download/238/230',
'https://soil-organisms.org/index.php/SO/article/download/240/231',
'https://soil-organisms.org/index.php/SO/article/download/225/218',
'https://soil-organisms.org/index.php/SO/article/download/226/219',
'https://soil-organisms.org/index.php/SO/article/download/228/220',
'https://soil-organisms.org/index.php/SO/article/download/229/221',
'https://soil-organisms.org/index.php/SO/article/download/230/222',
'https://soil-organisms.org/index.php/SO/article/download/231/223',
'https://soil-organisms.org/index.php/SO/article/download/203/196',
'https://soil-organisms.org/index.php/SO/article/download/192/183',
'https://soil-organisms.org/index.php/SO/article/download/193/184',
'https://soil-organisms.org/index.php/SO/article/download/194/185',
'https://soil-organisms.org/index.php/SO/article/download/195/186',
'https://soil-organisms.org/index.php/SO/article/download/196/187',
'https://soil-organisms.org/index.php/SO/article/download/197/188',
'https://soil-organisms.org/index.php/SO/article/download/198/189',
'https://soil-organisms.org/index.php/SO/article/download/199/190',
'https://soil-organisms.org/index.php/SO/article/download/200/191',
'https://soil-organisms.org/index.php/SO/article/download/184/177',
'https://soil-organisms.org/index.php/SO/article/download/185/178',
'https://soil-organisms.org/index.php/SO/article/download/186/179',
'https://soil-organisms.org/index.php/SO/article/download/187/180',
'https://soil-organisms.org/index.php/SO/article/download/188/181',
'https://soil-organisms.org/index.php/SO/article/download/189/182',
'https://soil-organisms.org/index.php/SO/article/download/107/88',
'https://soil-organisms.org/index.php/SO/article/download/108/87',
'https://soil-organisms.org/index.php/SO/article/download/109/86',
'https://soil-organisms.org/index.php/SO/article/download/110/85',
'https://soil-organisms.org/index.php/SO/article/download/111/91',
'https://soil-organisms.org/index.php/SO/article/download/112/90',
'https://soil-organisms.org/index.php/SO/article/download/120/93',
'https://soil-organisms.org/index.php/SO/article/download/113/95',
'https://soil-organisms.org/index.php/SO/article/download/121/104',
'https://soil-organisms.org/index.php/SO/article/download/119/111',
'https://soil-organisms.org/index.php/SO/article/download/123/98',
'https://soil-organisms.org/index.php/SO/article/download/124/118',
'https://soil-organisms.org/index.php/SO/article/download/125/119',
'https://soil-organisms.org/index.php/SO/article/download/115/127',
'https://soil-organisms.org/index.php/SO/article/download/127/121',
'https://soil-organisms.org/index.php/SO/article/download/128/130',
'https://soil-organisms.org/index.php/SO/article/download/129/125',
'https://soil-organisms.org/index.php/SO/article/download/130/128',
'https://soil-organisms.org/index.php/SO/article/download/135/136',
'https://soil-organisms.org/index.php/SO/article/download/133/131',
'https://soil-organisms.org/index.php/SO/article/download/141/132',
'https://soil-organisms.org/index.php/SO/article/download/142/134',
'https://soil-organisms.org/index.php/SO/article/download/152/140',
'https://soil-organisms.org/index.php/SO/article/download/140/141',
'https://soil-organisms.org/index.php/SO/article/download/150/147',
'https://soil-organisms.org/index.php/SO/article/download/155/146',
'https://soil-organisms.org/index.php/SO/article/download/151/151',
'https://soil-organisms.org/index.php/SO/article/download/164/155',
'https://soil-organisms.org/index.php/SO/article/download/147/156',
'https://soil-organisms.org/index.php/SO/article/download/156/157',
'https://soil-organisms.org/index.php/SO/article/download/154/153',
'https://soil-organisms.org/index.php/SO/article/download/159/158',
'https://soil-organisms.org/index.php/SO/article/download/160/161',
'https://soil-organisms.org/index.php/SO/article/download/167/168',
'https://soil-organisms.org/index.php/SO/article/download/166/164',
'https://soil-organisms.org/index.php/SO/article/download/165/163',
'https://soil-organisms.org/index.php/SO/article/download/158/162',
'https://soil-organisms.org/index.php/SO/article/download/170/166',
'https://soil-organisms.org/index.php/SO/article/download/171/167',
'https://soil-organisms.org/index.php/SO/article/download/179/169',
'https://soil-organisms.org/index.php/SO/article/download/174/174',
'https://soil-organisms.org/index.php/SO/article/download/181/217',
'https://soil-organisms.org/index.php/SO/article/download/177/170',
'https://soil-organisms.org/index.php/SO/article/download/178/175',
'https://soil-organisms.org/index.php/SO/article/download/182/273',
'https://soil-organisms.org/index.php/SO/article/download/183/272',
'https://soil-organisms.org/index.php/SO/article/download/173/274',
'https://soil-organisms.org/index.php/SO/article/download/282/275',
'https://soil-organisms.org/index.php/SO/article/download/283/287',
'https://soil-organisms.org/index.php/SO/article/download/303/289',
'https://soil-organisms.org/index.php/SO/article/download/306/292',
'https://soil-organisms.org/index.php/SO/article/download/304/290',
'https://soil-organisms.org/index.php/SO/article/download/300/288',
'https://soil-organisms.org/index.php/SO/article/download/307/295',
'https://soil-organisms.org/index.php/SO/article/download/308/297',
'https://soil-organisms.org/index.php/SO/article/download/309/298',
'https://soil-organisms.org/index.php/SO/article/download/311/299',
'https://soil-organisms.org/index.php/SO/article/download/301/300',
'https://soil-organisms.org/index.php/SO/article/download/312/301',
'https://soil-organisms.org/index.php/SO/article/download/310/310',
'https://soil-organisms.org/index.php/SO/article/download/315/312',
'https://soil-organisms.org/index.php/SO/article/download/316/314',
'https://soil-organisms.org/index.php/SO/article/download/328/315',
'https://soil-organisms.org/index.php/SO/article/download/202/192',
'https://soil-organisms.org/index.php/SO/article/download/24/23',
'https://soil-organisms.org/index.php/SO/article/download/25/22',
'https://soil-organisms.org/index.php/SO/article/download/204/197',
'https://soil-organisms.org/index.php/SO/article/download/205/198',
'https://soil-organisms.org/index.php/SO/article/download/206/199',
'https://soil-organisms.org/index.php/SO/article/download/207/200',
'https://soil-organisms.org/index.php/SO/article/download/208/201',
'https://soil-organisms.org/index.php/SO/article/download/209/202',
'https://soil-organisms.org/index.php/SO/article/download/210/203',
'https://soil-organisms.org/index.php/SO/article/download/211/204',
'https://soil-organisms.org/index.php/SO/article/download/212/205',
'https://soil-organisms.org/index.php/SO/article/download/213/206',
'https://soil-organisms.org/index.php/SO/article/download/214/207',
'https://soil-organisms.org/index.php/SO/article/download/215/208',
'https://soil-organisms.org/index.php/SO/article/download/216/209',
'https://soil-organisms.org/index.php/SO/article/download/217/210',
'https://soil-organisms.org/index.php/SO/article/download/218/211',
'https://soil-organisms.org/index.php/SO/article/download/219/212',
'https://soil-organisms.org/index.php/SO/article/download/220/213',
'https://soil-organisms.org/index.php/SO/article/download/221/214',
'https://soil-organisms.org/index.php/SO/article/download/222/215',
'https://soil-organisms.org/index.php/SO/article/download/224/216',
'https://soil-organisms.org/index.php/SO/article/download/358/332',
'https://soil-organisms.org/index.php/SO/article/download/330/324',
'https://soil-organisms.org/index.php/SO/article/download/335/328',
'https://soil-organisms.org/index.php/SO/article/download/338/325',
'https://soil-organisms.org/index.php/SO/article/download/333/331',
'https://soil-organisms.org/index.php/SO/article/download/349/329',
'https://soil-organisms.org/index.php/SO/article/download/357/385',
'https://soil-organisms.org/index.php/SO/article/download/356/391',
'https://soil-organisms.org/index.php/SO/article/download/409/384',
'https://soil-organisms.org/index.php/SO/article/download/362/387',
'https://soil-organisms.org/index.php/SO/article/download/417/405',
'https://soil-organisms.org/index.php/SO/article/download/414/407',
'https://soil-organisms.org/index.php/SO/article/download/416/401',
'https://soil-organisms.org/index.php/SO/article/download/415/396',
'https://soil-organisms.org/index.php/SO/article/download/413/406',
'https://soil-organisms.org/index.php/SO/article/download/431/422',
'https://soil-organisms.org/index.php/SO/article/download/433/413',
'https://soil-organisms.org/index.php/SO/article/download/435/410',
'https://soil-organisms.org/index.php/SO/article/download/432/414',
'https://soil-organisms.org/index.php/SO/article/download/429/409',
'https://soil-organisms.org/index.php/SO/article/download/438/412',
'https://soil-organisms.org/index.php/SO/article/download/434/411',
'https://soil-organisms.org/index.php/SO/article/download/11/11',
'https://soil-organisms.org/index.php/SO/article/download/12/12',
'https://soil-organisms.org/index.php/SO/article/download/7/4',
'https://soil-organisms.org/index.php/SO/article/download/6/5',
'https://soil-organisms.org/index.php/SO/article/download/5/6',
'https://soil-organisms.org/index.php/SO/article/download/4/7',
'https://soil-organisms.org/index.php/SO/article/download/3/8',
'https://soil-organisms.org/index.php/SO/article/download/1/1',
'https://soil-organisms.org/index.php/SO/article/download/54/33',
'https://soil-organisms.org/index.php/SO/article/download/55/34',
'https://soil-organisms.org/index.php/SO/article/download/56/35',
'https://soil-organisms.org/index.php/SO/article/download/57/36',
'https://soil-organisms.org/index.php/SO/article/download/58/37',
'https://soil-organisms.org/index.php/SO/article/download/59/38',
'https://soil-organisms.org/index.php/SO/article/download/60/39',
'https://soil-organisms.org/index.php/SO/article/download/61/40',
'https://soil-organisms.org/index.php/SO/article/download/62/41',
'https://soil-organisms.org/index.php/SO/article/download/64/42',
'https://soil-organisms.org/index.php/SO/article/download/65/43',
'https://soil-organisms.org/index.php/SO/article/download/66/44',
'https://soil-organisms.org/index.php/SO/article/download/67/139',
'https://soil-organisms.org/index.php/SO/article/download/68/46',
'https://soil-organisms.org/index.php/SO/article/download/69/47',
'https://soil-organisms.org/index.php/SO/article/download/70/48',
'https://soil-organisms.org/index.php/SO/article/download/71/49',
'https://soil-organisms.org/index.php/SO/article/download/73/50',

);

$urls=array(
'https://soil-organisms.org/SO/article/view/286/276'
);

$count = 1;

echo "url\tresponse\n";

foreach ($urls as $url)
{
	$http_code = get($url);
	
	echo "$url\t$http_code\n";
	
	// Give server a break every 10 items
	if (($count++ % 10) == 0)
	{
		$rand = rand(1000000, 3000000);
		//echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
		usleep($rand);
	}	
}

?>
