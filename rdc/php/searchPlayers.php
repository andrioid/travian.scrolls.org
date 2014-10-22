<?php
ini_set('display_errors', 1);
error_reporting(-1);
include("bootstrap.php");
$x = 0;
$y = 0;
$ServerList = betterServerlist($Servers);
if (!isset($_REQUEST['server'])) { $_REQUEST['server'] = 's1.travian.dk'; }
foreach ($ServerList as $s) {
	if (isset($_REQUEST['server']) && strtolower($s['name']) == strtolower($_REQUEST['server'])) { 
		$server = $s; 
	} else {
	}
}
if (!isset($server) or !isset($_GET['x']) or !isset($_GET['y'])) { die("No server found"); }
$l = new SystemList();
$thisyear = (int)date('Y');
$thisweek = (int)date('W');
if ($thisweek == 1) { $lastweek = 53; $lastyear = $thisyear-1; } else { $lastweek = $thisweek-1; $lastyear=$thisyear; }
$thisdate = $thisyear.$thisweek;
$lastdate = $lastyear.$lastweek;

$query = sprintf("SELECT village, alliance, player, v.population as population, SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d)) as distance, x, y, tid, v.uid as uid, aid, vid, t.population as tpop, t.population-l.population as growth FROM %s as v LEFT JOIN %s_upop as t ON v.uid=t.uid AND t.week=%s LEFT JOIN %s_upop as l ON v.uid=l.uid AND l.week=%s", $_GET['x'], $_GET['x'], $_GET['y'], $_GET['y'], $server['table'], $server['table'], $thisdate, $server['table'], $lastdate);
//printf("query: %s <br>\n", $query);
$l->setQuery($query);
$l->setCache(0);
$l->setOrder('distance', 'ASC');
//$l->setGroupBy('uid');
if (!empty($_GET['size'])) { $l->addCondition('population', (int)$_GET['size'], '>', 'and', false); }
if (!empty($_GET['a'])) {
	if (strstr($_GET['a'], ',')) { $a = explode(',', $_GET['a']); } else { $a = array($_GET['a']); }
	$join = false;
	$joiner = 'and';
	foreach ($a as $alliance) {
		$alliance = trim($alliance);
		$l->addCondition('alliance', $alliance, '=', $joiner, $join);
		$join = true;
		$joiner = 'or';
	}
}
if (!empty($_GET['p'])) {
	if (strstr($_GET['p'], ',')) { $p = explode(',', $_GET['p']); } else { $p = array($_GET['p']); }
	$join = false;
	$joiner = 'or';
	foreach ($p as $player) {
		$player = trim($player);
		$l->addCondition('player', $player, '=', $joiner, $join);
		$join = true;
		$joiner = 'or';
	}
}
if (!empty($_GET['idle']) and $_GET['idle'] == 1) { 
	$l->addCondition('t.population-l.population', 0, '=', 'and', false);
}
$l->setPaging(1,200);
$l->populate();
echo json_encode($l->list);

?>
