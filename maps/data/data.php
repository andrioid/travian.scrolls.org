<?php

include("../../../travian.scrolls.org/config.php"); // our travian.scrolls.org config
include("functions.php");
ini_set('display_errors',1);
//apc_clear_cache('user');
$cacheTime = 60*60*12;
$multiplier = 80; // must match with javascript
mb_internal_encoding("UTF-8");

if (!strstr($_GET['q'], '/')) {
	$server = 's3.travian.dk';
	$x = 0;
	$y = 0;
	printf("Not implemented yet...");
	exit(0);
} else {
	if (!preg_match('/^(?P<server>[a-z.0-9]+)\/(?P<x>(\-?)[0-9]+)_(?P<y>(\-?)[0-9]+)\.json$/', $_GET['q'], $matches)) {
		printf("%s <br>\n", $_GET['q']);
		printf("Invalid request reg<br>\n"); exit(0);
	} else {
		$server = $matches['server'];
		$x = (int)$matches['x'];
		$y = (int)$matches['y'];
	}
}
//printf("Server: %s (%d,%d)<br>\n", $server, $x, $y);

// Find table for server
$table = null;
foreach ($Servers as $s) {
	if (preg_match('/^http:\/\/(?P<server>[a-z.0-9]+)/', $s['dumpfile'], $matches)) {
		if ($matches['server'] == $server) {
			$table = $s['name'];
			break;
		}
	}
}
if ($table == null) { printf("Unable to find table for server, probably not indexed"); exit(0); }

$cacheName = sprintf("%s-%s-(%d,%d)", 'travmap', $server, $x, $y);
if ($data = fromCache($cacheName)) {
	header("Expires: " . gmdate( "D, d M Y H:i:s", time()+60*60*1) . "GMT" );
	header("Content-type: application/json");
	echo json_encode($data);
	exit(0);
}

// Fix the query
$sx = floor($x*$multiplier);
$sy = floor($y*$multiplier);
$k = $x+1;
$j = $y+1;
$ex = ceil($k*$multiplier);
$ey = ceil($j*$multiplier);

$query = sprintf("SELECT * FROM %s WHERE x>=%d AND x<=%d AND y>=%d and y<=%d", $table, $sx, $ex, $sy, $ey);

//printf("query: %s<br>\n", $query);

$db = new mysqli($DB['host'], $DB['user'], $DB['pass'], 'travian');

if (!$result = $db->query($query)) { die("Query failed:".$db->error); }

//printf("numrows: %d<br>", $result->num_rows);

//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
//header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
//header("Cache-Control: no-cache, must-revalidate" ); 
//header("Pragma: no-cache" );
header("Expires: " . gmdate( "D, d M Y H:i:s", time()+$cacheTime) . "GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s", time()+$cacheTime) . "GMT" );
header("Content-type: application/json");

$r = array();
while ($obj = $result->fetch_object()) {
	$r[] = array(
		'x' => $obj->x,
		'y' => $obj->y,
		'village' => utf8_encode($obj->village),
		'player' => utf8_encode($obj->player),
		'alliance' => utf8_encode($obj->alliance),
		'population' => $obj->population,
		'aid' => $obj->aid
	);
}
toCache($cacheName, $r);
echo json_encode($r);

?>

