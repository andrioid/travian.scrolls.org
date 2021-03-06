<?php

include("../config.php");
include("../functions.php");

$pDBhost = mysql_connect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");
if (!isset($_GET['sid'])) { 
	$sid = 14; 
} else {
	$sid = sprintf("%s", $_GET['sid']);
}
$table = $Servers[$sid]['name'];
$poptable = $Servers[$sid]['name']."_upop";


if (!empty($_GET['a'])) { 
	$alliances = $_GET['a']; 
	$Syntax = a2syntax($alliances);
} else {
	$Syntax = "aid=0";
	$alliances = "";
}

$current_week = date("W", time());

if (!isset($_GET['week_nr'])) { 
	$week_nr = date("W", time()); 
} else {
	$week_nr = sprintf("%d", $_GET['week_nr']);
}

$stable = "dk_s5";

/* Average Values */
$Top20_query = sprintf("SELECT sum(avg_top20)/20 from (
	select alliance, count(distinct uid) count, sum(population)/count(distinct uid) as avg_top20 from %s 
	WHERE aid!=0 
	GROUP BY aid 
	ORDER by sum(population) DESC 
	limit 0,20
	) as top20"
	, $table);
$tquery = mysql_query($Top20_query) or die(mysql_error());
list($t20avg) = mysql_fetch_row($tquery);

$Top10_query = sprintf("SELECT sum(avg_top10)/10 from (
	select alliance, count(distinct uid) count, sum(population)/count(distinct uid) as avg_top10 from %s 
	WHERE aid!=0 
	GROUP BY aid 
	ORDER by sum(population) DESC 
	limit 0,10
	) as top20"
	, $table);
$tquery = mysql_query($Top10_query) or die(mysql_error());
list($t10avg) = mysql_fetch_row($tquery);

$S_query = sprintf("SELECT sum(avg_top10)/count(popcount) from (
	select alliance, count(distinct uid) popcount, sum(population)/count(distinct uid) as avg_top10 from %s 
	WHERE aid!=0 AND (%s) 
	GROUP BY aid 
	ORDER by sum(population) DESC 
	limit 0,10
	) as top"
	, $table, a2syntax($alliances));
$tquery = mysql_query($S_query) or die(mysql_error());
list($savg) = mysql_fetch_row($tquery);


/* Member list */
$Query_Members = sprintf("select uid,player,alliance,tid from %s where (%s) group by uid order by sum(population) DESC limit 0,500", $table, a2syntax($alliances));
$Result_Members = mysql_query($Query_Members) or die(mysql_error());
//printf("Query (%s) <br>\n", $Query_Members);
if (mysql_num_rows($Result_Members) > 0) {
	while ($row = mysql_fetch_array($Result_Members)) {
		$uid = $row['uid'];
		$Members[$uid]['player'] = $row['player'];
		$Members[$uid]['alliance'] = $row['alliance'];
		$Members[$uid]['tid'] = $row['tid'];
	}
	
}

/* Population data */
$Query_Population = sprintf("select uid, population, week FROM %s_upop WHERE (week=%d or week=%d)", $table, $week_nr, $week_nr-1);
$Result_Population = mysql_query($Query_Population) or die(mysql_error());

#printf("Query: %s<br>\n", $Query_Population);
//printf("Numrows (%s)<br>\n", mysql_num_rows($Result_Population));
if (mysql_num_rows($Result_Population) > 0) {
	
	while ($row = mysql_fetch_array($Result_Population)) {
		if (count($Members[$row['uid']]) > 0) {
			$uid = $row['uid'];
			//printf("setting pop for %s <br>\n", $row['uid']);
			$Population[$uid][$row['week']] = $row['population'];
			
		}
		
	}
}

/* Calculate growth between those two weeks */
if (count($Members) > 0) {
	foreach ($Members as $uid=>$marray) {
		//printf("Debug: %s %s <br>\n", $uid, $Population[$week_nr]-$Population[$week_nr-1]);
		$Member_Growth[$uid] = $Population[$uid][$week_nr]-$Population[$uid][$week_nr-1];
	}
	arsort($Member_Growth, SORT_DESC);
}


?>
<HTML>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<TITLE>Travian Alliance Statistics - Grettir's Travian Toolchest</TITLE>
	<link rel=stylesheet type="text/css" href="../calc.css">
</HEAD>
<BODY>
<?php include("../topbanner.php"); ?>
<div style="float: left; margin-right: 100px;">
<h3>Player Growth Statistics (Week <?php echo $week_nr-1; ?>)</h3>
<form method="get">
Alliances:<br>
<input type=text name="a" value="<?php echo $_GET['a']; ?>">
<select name="sid">
	<?php $tslist = $Servers; ?>
	<?php foreach ($tslist as $i=>$server) {
				if ($i == $sid) {
					printf("<option SELECTED value=\"%d\">%s</option>", $i, $server['name']);
				} else {
					printf("<option value=\"%d\">%s</option>", $i, $server['name']);
				}
			}
	?>
</select>
<input type="submit">
</form>
</div>
<div style="float: left; width: 450px; margin-top: 10px;">
<?php if (count($Member_Growth) > 0) { ?>
	<strong>Status icons (on the right)</strong><br>
	Gold: Player's population is above the calculated average of top 10 alliances (<?php printf("%d", $t10avg); ?>)<br>
	Silver: Player's population is above the calculated average of top 20 alliances (<?php printf("%d", $t20avg); ?>)<br>
	Chart (green): Player's population is above the query average (<?php printf("%d", $savg); ?>)<br>
	Chart (warning): Player's population is below the query average<br>
</div>
<br style="clear: left;">
	<?php if ($current_week >= $week_nr) { printf("[<a href=\"./?a=%s&sid=%d&week_nr=%s\">Last Week</a>]", $_GET['a'], $sid, $week_nr-1); } ?>
	<?php if ($current_week > $week_nr) { printf(" [<a href=\"./?a=%s&sid=%d&week_nr=%s\">Next Week</a>]", $_GET['a'], $sid, $week_nr+1); } ?><br>

	<table>
	<tr class="rbg"><td>&nbsp;</td><td>Rank</td><td>Name</td><td>Alliance</td><td>Growth</td><td>Population</td></tr>
	<?php
	$i = 1;
	foreach($Member_Growth as $uid=>$growth) {
		$medal = "";
		if ($Population[$uid][$week_nr-1] != 0) { 
			$prec = $growth/$Population[$uid][$week_nr-1]*100;
			$tid = $Members[$uid]['tid'];
			$icon = $Races[$tid]['units'][9]['image'];
			if ($growth < 0) { $ngrowth = sprintf("<span style=\"color: #BB0000\">%d</span>", $growth); }
			elseif ($growth < 50) { $ngrowth = sprintf("<span style=\"color: #FF6633\">+%d</span>", $growth); }
			elseif ($growth < 200) { $ngrowth = sprintf("<span style=\"color: #000000\">+%d</span>", $growth); }
			elseif ($growth >= 200) { $ngrowth = sprintf("<span style=\"color: #336600\">+%d</span>", $growth); }
			
			if ($Population[$uid][$week_nr] >= $t10avg) { $medal = "<img src=\"/img/star1.gif\" border=\"0\">"; }
			elseif ($Population[$uid][$week_nr] >= $t20avg) { $medal = "<img src=\"/img/star2.gif\" border\"0\">"; }			
			elseif ($Population[$uid][$week_nr] >= $savg) { $medal = "<img src=\"/img/chart_go.gif\" border\"0\">"; }
			elseif ($Population[$uid][$week_nr] < $savg) { $medal = "<img src=\"/img/chart_error.gif\" border\"0\">"; }
		} 
		//printf("<tr><td>%d</td><td>%s</td><td>%s</td><td>%01.2f%%</td><td>%d (%s)</td></tr>\n", $i, $Players[$uid]['player'], $Players[$uid]['alliance'], $Players[$uid]['growth'], $Players[$uid]['population'], $Players[$uid]['difference']);
		printf("<tr><td>%s</td><td>%d</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%01.2f%%</td><td>%d (%s)</td><td>%s</td></tr>\n", "<img src=".$icon." border=\"0\">", $i, $Members[$uid]['player'], $Members[$uid]['alliance'], $prec, $Population[$uid][$week_nr], $ngrowth, $medal);
		$i++;
	}
	?></table>
	<?php if ($current_week >= $week_nr) { printf("[<a href=\"./?a=%s&sid=%d&week_nr=%s\">Last Week</a>]", $_GET['a'], $sid, $week_nr-1); } ?>
	<?php if ($current_week > $week_nr) { printf(" [<a href=\"./?a=%s&sid=%d&week_nr=%s\">Next Week</a>]", $_GET['a'], $sid, $week_nr+1); } ?><br>

<?php } ?>
</body>
</html>
