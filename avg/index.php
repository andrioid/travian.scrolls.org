<?php

<?php

include("../config.php");
include("../functions.php");

$pDBhost = mysql_connect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");
$sid = 3;
$table = $Servers[$sid]['name'];
$poptable = $Servers[$sid]['name']."_upop";
$alliances = "K-WTH";
if (!empty($_GET['a'])) { $alliances = $_GET['a']; }
$Syntax = a2syntax($alliances);

/* Top 20 average population */

SELECT sum(avg_top20)/10 from (
$Top20_query = sprintf("SELECT sum(avg_top20)/20 from (
	select alliance, count(distinct uid) count, sum(population)/count(distinct uid) as avg_top20 from %s 
	WHERE aid!=0 
	GROUP BY aid 
	ORDER by sum(population) DESC 
	limit 0,20
	) as top20"
	, $table);
$Top10_query = sprintf("SELECT sum(avg_top10)/10 from (
	select alliance, count(distinct uid) count, sum(population)/count(distinct uid) as avg_top10 from %s 
	WHERE aid!=0 
	GROUP BY aid 
	ORDER by sum(population) DESC 
	limit 0,10
	) as top20"
	, $table);



$Query_Template = "SELECT alliance, avg(population) FROM `dk_s5` where alliance != '' group by aid order by sum(population) DESC limit 0,20";

?>