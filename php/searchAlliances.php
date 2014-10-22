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

$query = sprintf(
	"SELECT alliance,sum(population) as populationCount, AVG(SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d))) as avgDistance, MAX(SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d))) as maxDistance, MIN(SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d))) as minDistance, count(id) as villageCount, aid, MAX(population) as maxVillage, AVG(population) as avgVillage FROM %s", 
	$_GET['x'], $_GET['x'], $_GET['y'], $_GET['y'], $_GET['x'], $_GET['x'], $_GET['y'], $_GET['y'], $_GET['x'], $_GET['x'], $_GET['y'], $_GET['y'], $server['table']
);
$l->setQuery($query);
$l->setGroupBy('aid');
$l->setCache(0);
$l->setOrder('populationCount', 'DESC');
$l->addCondition('aid', 0, '>', 'and', false);
if (!empty($_GET['maxd'])) {
	$cond = sprintf("SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d))", $_GET['x'], $_GET['x'], $_GET['y'], $_GET['y']);
	$l->addCondition($cond, (int)$_GET['maxd'], '<', 'and', false);
}

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
$l->setPaging(1,200);
$l->populate();
echo json_encode($l->list);

?>
