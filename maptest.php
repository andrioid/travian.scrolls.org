<?php
    #$db = @mysql_connect($mysqlhost, $mysqluser, $mysqlpass) OR die('Can not connect to DB-Server!');
    #$db_select = @mysql_select_db($mysqldb) OR die('Can not select DB!');
		include("/home/andri/travian.scrolls.org/config.php");
		include("/home/andri/travian.scrolls.org/functions.php");

$pDBhost = mysql_pconnect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");

if (isset($_GET['sid'])) {
	$sid = $_GET['sid'];
	$table = $Servers[$sid]['name'];
} else {
	$table = $Servers[14]['name'];
}

$image = imagecreatetruecolor(801, 801);
    
// Fill images background with chosen color
imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));


function xp ($x) {
	return 401+($x);
}

function yp ($y) {
	return 401+($y);
}

function marker ($x, $y, $size, $color) {
	global $image, $Alliance, $ColorIndex;
	$nsize = $size/2;
	$nx = 401+($x)-$nsize;
	$ny = 401-($y)-$nsize;
	$px = $nx+$nsize;
	$py = $ny+$nsize;
	//imagefilledrectangle($image, xp(-400), yp(0), xp(-400)+10, yp(0)+10, $color_marked);	
	imagefilledrectangle($image, $nx, $ny, $px, $py, $color);	
}

function dot ($x, $y, $color) {
	global $image, $Alliance, $ColorIndex;
	$nx = 401+($x);
	$ny = 401-($y);
	$px = $nx+1;
	$py = $ny+1;
	//imagefilledrectangle($image, xp(-400), yp(0), xp(-400)+10, yp(0)+10, $color_marked);	
	imagefilledrectangle($image, $nx, $ny, $px, $py, $color);	
}

function marktest ($x, $y, $size, $text, $color) {
	global $image, $Alliance, $font;
	$nsize = $size/2;
	$nsize = 0;
	$nx = 401+($x)-$nsize;
	$ny = 401-($y)-$nsize;
	//imagestring($image, 1, $nx, $ny, " ".$text, imagecolorallocate($image,255,255,255));
	imagettftext($image, 7, 0, $nx-1, $ny-1, $color[255], $font, $text);
	//imagettftext($image, 7, 0, $nx+1, $ny+1, $color[255], $font, $text);
	imagettftext($image, 7, 0, $nx, $ny, $color[257], $font, utf8_encode($text));
	
}

$ColorIndex[] = imagecolorallocate($image, 255, 0, 0);
$ColorIndex[] = imagecolorallocate($image, 204, 0, 0);
$ColorIndex[] = imagecolorallocate($image, 245, 61, 0);
//$ColorIndex[] = imagecolorallocate($image, 0, 255, 0);
$ColorIndex[] = imagecolorallocate($image, 255, 150, 0);
$ColorIndex[] = imagecolorallocate($image, 90, 0, 173);
$ColorIndex[] = imagecolorallocate($image, 0, 0, 102);
$ColorIndex[] = imagecolorallocate($image, 0, 255, 255);
$ColorIndex[] = imagecolorallocate($image, 255, 204, 255);
$ColorIndex[] = imagecolorallocate($image, 0, 102, 51);
$ColorIndex[] = imagecolorallocate($image, 0, 102, 153);
$ColorIndex[] = imagecolorallocate($image, 255, 102, 0);
$ColorIndex[] = imagecolorallocate($image, 255, 102, 102);
$ColorIndex[] = imagecolorallocate($image, 102, 0, 0);
//$ColorIndex[] = imagecolorallocate($image, 0, 102, 0);
$ColorIndex[] = imagecolorallocate($image, 0, 102, 102);
$ColorIndex[] = imagecolorallocate($image, 153, 0, 153);
$ColorIndex[] = imagecolorallocate($image, 153, 255, 153);
$ColorIndex[] = imagecolorallocate($image, 255, 204, 153);
$ColorIndex[] = imagecolorallocate($image, 102, 51, 51);
$ColorIndex[] = imagecolorallocate($image, 102, 153, 102);
$ColorIndex[] = imagecolorallocate($image, 51, 77, 102);
$ColorIndex[] = imagecolorallocate($image, 173, 90, 0);
$ColorIndex[255] = imagecolorallocate($image, 0, 0, 0);
$ColorIndex[256] = imagecolorallocate($image, 99, 99, 99);
$ColorIndex[257] = imagecolorallocate($image, 255,255,255);

$font = "Andale_Mono.ttf";

$Query = sprintf("SELECT aid, alliance, sum(population) as pop FROM %s WHERE aid!=0 GROUP BY aid ORDER BY sum(population) DESC LIMIT 0,20", $table);
$Result = mysql_query($Query) or die(mysql_error());
$i = 0;
while(list($aid, $alliance, $pop) = mysql_fetch_row($Result)) {
	$Alliance[$aid] = array(
		'alliance' => $alliance, 
		'population' => $pop,
		'color' => $ColorIndex[$i]
	);
	if (!empty($Alliance[$aid]['alliance'])) {
		if (!isset($ColorIndex[$i+1])) { 
			$i = 255; 
		} else { 
			$i++; 
		}
	}
}

//echo "<pre>"; print_r($Alliance); echo "</pre>";


$i = 0;
for ($ty=-400; $ty<=400; $ty=$ty+10) {
	for ($tx=-400; $tx<=400; $tx=$tx+10) {
		$basequery = sprintf("SELECT aid FROM `%s` WHERE aid!=0 and SQRT(POW(ABS(x-%d),2)+POW(ABS(y-%d),2)) < %d GROUP BY aid ORDER BY sum(population) DESC limit 0,1", $table, $tx, $ty, 20);
		$Result = mysql_query($basequery) or die(mysql_error());
		//printf("%d,%d - %s<br>\n", $tx, $ty, $Alliance[$aid]['alliance']);
		//$ty = 9000;
		list($aid) = mysql_fetch_row($Result);
		if (!empty($Alliance[$aid]['alliance'])) {
			//marker($tx, $ty, 20, $Alliance[$aid]['color']);
			marker($tx, $ty, 20, pow($aid,3));
			if ($aid != $Old['aid']) { 
				marktest($Old['x'], $Old['y'], 20, $Alliance[$Old['aid']]['alliance'], $ColorIndex); 
				$Old['x'] = $tx;
				$Old['y'] = $ty;
				$Old['aid'] = $aid;
			}
		}
		if (isset($ColorIndex[$i+1])) { $i++; } else { $i = 0; }
		$aid = 0;

	}
}
for ($y=-400; $y<=400; $y++) {
	for ($x=-400; $x<=400; $x++) {
		if ($x == 0 || $y == 0) { dot($x, $y, $ColorIndex[256]); }
	}
}
    
    // Select the HTTP-Header for the selected filetype
    header("Content-Type: image/png");
    
    // Generate image and print it
    imagepng($image);
//    imagepng($image,"../travian.scrolls.org/maps/".$table.".png");


?> 
