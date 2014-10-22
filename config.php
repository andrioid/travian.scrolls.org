<?PHP

# Database settings
$DB['host'] = "localhost";
$DB['user'] = "login";
$DB['pass'] = "password";

date_default_timezone_set("Europe/Copenhagen");

$LogFile = "login.log";

# Server List
$Servers[0]['name'] = "dk_s1";
$Servers[0]['dumpfile'] = "http://s1.travian.dk/map.sql";
$Servers[1]['name'] = "dk_s2";
$Servers[1]['dumpfile'] = "http://s2.travian.dk/map.sql";
$Servers[2]['name'] = "dk_s3";
$Servers[2]['dumpfile'] = "http://s3.travian.dk/map.sql";
$Servers[3]['name'] = "dk_s4";
$Servers[3]['dumpfile'] = "http://s4.travian.dk/map.sql";
$Servers[4]['name'] = "dk_speed";
$Servers[4]['dumpfile'] = "http://speed.travian.dk/map.sql";
$Servers[5]['name'] = "com_s1";
$Servers[5]['dumpfile'] = "http://s1.travian.com/map.sql";
$Servers[6]['name'] = "com_s2";
$Servers[6]['dumpfile'] = "http://s2.travian.com/map.sql";
$Servers[7]['name'] = "com_s3";
$Servers[7]['dumpfile'] = "http://s3.travian.com/map.sql";
$Servers[8]['name'] = "com_speed";
$Servers[8]['dumpfile'] = "http://speed.travian.com/map.sql";
$Servers[9]['name'] = "com_s4";
$Servers[9]['dumpfile'] = "http://s4.travian.com/map.sql";
$Servers[10]['name'] = "com_s5";
$Servers[10]['dumpfile'] = "http://s5.travian.com/map.sql";
$Servers[11]['name'] = "com_s6";
$Servers[11]['dumpfile'] = "http://s6.travian.com/map.sql";
$Servers[12]['name'] = "com_s7";
$Servers[12]['dumpfile'] = "http://s7.travian.com/map.sql";
$Servers[13]['name'] = "uk_speed";
$Servers[13]['dumpfile'] = "http://speed.travian.co.uk/map.sql";
$Servers[15] = array(
	'name' => "com_s8",
	'dumpfile' => "http://s8.travian.com/map.sql",
);
$Servers[16] = array(
	'name' => "lt_s1",
	'dumpfile' => "http://s1.travian.lt/map.sql",
);
$Servers[17] = array(
	'name' => "uk_s4",
	'dumpfile' => "http://s4.travian.co.uk/map.sql",
);
$Servers[18] = array(
	'name' => "com_x2",
	'dumpfile' => "http://x2.travian.com/map.sql",
);
$Servers[] = array(
	'name' => "cz_s4",
	'dumpfile' => "http://s4.travian.cz/map.sql",
);
$Servers[] = array(
	'name' => "ru_s3",
	'dumpfile' => "http://s3.travian.ru/map.sql",
);

$Servers[] = array(
	'name' => 'speed_hk',
	'dumpfile' => 'http://speed.travian.hk/map.sql',
);

$Servers[] = array(
	'name' => 'speed_br',
	'dumpfile' => 'http://speed.travian.com.br/map.sql',
);
$Servers[] = array(
	'name' => 'at',
	'dumpfile' => 'http://www.travian.at/map.sql',
);

$Servers[] = array(
	'name' => 'welt1.travian.de',
	'dumpfile' => 'http://welt1.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'welt2.travian.de',
	'dumpfile' => 'http://welt2.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'welt3.travian.de',
	'dumpfile' => 'http://welt3.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'welt4.travian.de',
	'dumpfile' => 'http://welt4.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'welt5.travian.de',
	'dumpfile' => 'http://welt5.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'welt6_de',
	'dumpfile' => 'http://welt6.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'http://welt7_de',
	'dumpfile' => 'http://welt7.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'http://welt8_de',
	'dumpfile' => 'http://welt8.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'http://welt9_de',
	'dumpfile' => 'http://welt9.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'speed_de',
	'dumpfile' => 'http://speed.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 'x2_de',
	'dumpfile' => 'http://x2.travian.de/map.sql',
);

$Servers[] = array(
	'name' => 's3_ba',
	'dumpfile' => 'http://s3.travian.ba/map.sql',
);

$AllowReferer = array(
	'scrolls.org',
);
$BlockList = array();

$sBlockList = array();

$WhiteList = array(
	'2211' => 'Andri',
	'5423' => 'Schbleh',
	'12345' => 'Johndixi',
	'9991' => 'AskeK',
);







/* Units: class (1, defensive, 2, offensive, 3, other) */
/* Romans */
$Units['Legionær'] = array('image' => "/img/1.gif", 'class' => 2);
$Units['Prætorianer'] = array('image' => "/img/2.gif", 'class' => 2);
$Units['Imperianer'] = array('image' => "/img/3.gif", 'class' => 2);
$Units['Equites Legati'] = array('image' => "/img/4.gif", 'class' => 3);
$Units['Equites Imperatoris'] = array('image' => "/img/5.gif", 'class' => 2);
$Units['Equites Caesaris'] = array('image' => "/img/6.gif", 'class' => 2);
$Units['Rambuk'] = array('image' => "/img/7.gif", 'class' => 2);
$Units['Brandkatapult'] = array('image' => "/img/8.gif", 'class' => 2);
$Units['Senator'] = array('image' => "/img/9.gif", 'class' => 2);
$Units['Bosætter'] = array('image' => "/img/20.gif", 'class' => 2);
/* Germans */
$Units['Køllesvinger'] = array('image' => "/img/11.gif", 'class' => 2);
$Units['Spydkæmper'] = array('image' => "/img/12.gif", 'class' => 2);
$Units['Øksekæmper'] = array('image' => "/img/13.gif", 'class' => 2);
$Units['Spejder'] = array('image' => "/img/14.gif", 'class' => 3);
$Units['Paladin'] = array('image' => "/img/15.gif", 'class' => 2);
$Units['Teutonrytter'] = array('image' => "/img/16.gif", 'class' => 2);
$Units['Rambuk'] = array('image' => "/img/27.gif", 'class' => 2);
$Units['Katapult'] = array('image' => "/img/18.gif", 'class' => 2);
$Units['Stammefører'] = array('image' => "/img/19.gif", 'class' => 2);
$Units['Bosætter'] = array('image' => "/img/20.gif", 'class' => 2);
/* Gauls */
$Units['Falanks'] = array('image' => "/img/21.gif", 'class' => 2);
$Units['Sværdkæmper'] = array('image' => "/img/22.gif", 'class' => 2);
$Units['Spion'] = array('image' => "/img/23.gif", 'class' => 2);
$Units['Theutaterlyn'] = array('image' => "/img/24.gif", 'class' => 3);
$Units['Druiderytter'] = array('image' => "/img/25.gif", 'class' => 2);
$Units['Haeduaner'] = array('image' => "/img/26.gif", 'class' => 2);
$Units['Rambuktræ'] = array('image' => "/img/27.gif", 'class' => 2);
$Units['Krigskatapult'] = array('image' => "/img/28.gif", 'class' => 2);
$Units['Høvding'] = array('image' => "/img/29.gif", 'class' => 2);
$Units['Bosætter'] = array('image' => "/img/20.gif", 'class' => 2);

$Races[1] = array(
	'name' => "Romans", 
	'image' => "/img/9.gif",
	'units' => array(
		'1' => array(
			'name' => "Kylfukall",
			'image' => "/img/1.gif",
			'speed' => "7",
		),
		'2' => array(
			'name' => "Kylfukall",
			'image' => "/img/2.gif",
			'speed' => "7",
		),
		'3' => array(
			'name' => "Kylfukall",
			'image' => "/img/3.gif",
			'speed' => "7",
		),
		'4' => array(
			'name' => "Kylfukall",
			'image' => "/img/4.gif",
			'speed' => "7",
		),
		'5' => array(
			'name' => "Kylfukall",
			'image' => "/img/5.gif",
			'speed' => "7",
		),
		'6' => array(
			'name' => "Kylfukall",
			'image' => "/img/6.gif",
			'speed' => "7",
		),
		'7' => array(
			'name' => "Rambuk",
			'image' => "/img/7.gif",
			'speed' => "4",
		),
		'8' => array(
			'name' => "Catapult",
			'image' => "/img/8.gif",
			'speed' => "3",
		),
		'9' => array(
			'name' => "Foringinn",
			'image' => "/img/9.gif",
			'speed' => "4",
		),
		'10' => array(
			'name' => "Foringinn",
			'image' => "/img/10.gif",
			'speed' => "4",
		),
	),
);
$Races[2] = array(
	'name' => "Germans", 
	'image' => "/img/19.gif",
	'units' => array(
		'1' => array(
			'name' => "Kylfukall",
			'image' => "/img/11.gif",
			'speed' => '7',
		),
		'2' => array(
			'name' => "Kylfukall",
			'image' => "/img/12.gif",
			'speed' => '7',
		),
		'3' => array(
			'name' => "Kylfukall",
			'image' => "/img/13.gif",
			'speed' => '7',
		),
		'4' => array(
			'name' => "Kylfukall",
			'image' => "/img/14.gif",
			'speed' => '7',
		),
		'5' => array(
			'name' => "Kylfukall",
			'image' => "/img/15.gif",
			'speed' => '7',
		),
		'6' => array(
			'name' => "Kylfukall",
			'image' => "/img/16.gif",
			'speed' => '7',
		),
		'7' => array(
			'name' => "Rambuk",
			'image' => "/img/17.gif",
			'speed' => "4",
		),
		'8' => array(
			'name' => "Catapult",
			'image' => "/img/18.gif",
			'speed' => "3",
		),
		'9' => array(
			'name' => "Foringinn",
			'image' => "/img/19.gif",
			'speed' => "4",
		),
		'10' => array(
			'name' => "Foringinn",
			'image' => "/img/20.gif",
			'speed' => "4",
		),
	),
);
$Races[3] = array(
	'name' => "Gauls",
	'image' => "/img/29.gif",
	'units' => array(
		'1' => array(
			'name' => "Kylfukall",
			'image' => "/img/21.gif",
			'speed' => '7',
		),
		'2' => array(
			'name' => "Kylfukall",
			'image' => "/img/22.gif",
			'speed' => '7',
		),
		'3' => array(
			'name' => "Kylfukall",
			'image' => "/img/23.gif",
			'speed' => '7',
		),
		'4' => array(
			'name' => "Kylfukall",
			'image' => "/img/24.gif",
			'speed' => '7',
		),
		'5' => array(
			'name' => "Kylfukall",
			'image' => "/img/25.gif",
			'speed' => '7',
		),
		'6' => array(
			'name' => "Kylfukall",
			'image' => "/img/26.gif",
			'speed' => '7',
		),
		
		'7' => array(
			'name' => "Rambuk",
			'image' => "/img/27.gif",
			'speed' => "4",
		),
		'8' => array(
			'name' => "Catapult",
			'image' => "/img/28.gif",
			'speed' => "3",
		),
		'9' => array(
			'name' => "Foringinn",
			'image' => "/img/29.gif",
			'speed' => "5",
		),
		'10' => array(
			'name' => "Foringinn",
			'image' => "/img/30.gif",
			'speed' => "5",
		),
	),
);



?>
