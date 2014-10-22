<?PHP
include("../config.php");

mysql_connect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");

$maxd = 30;

if (!empty($_GET['x'])) { $x = sprintf("%d", $_GET['x']); }
if (!empty($_GET['y'])) { $y = sprintf("%d", $_GET['y']); }
if (!empty($_GET['maxd'])) { $maxd = sprintf("%d", $_GET['maxd']); }
if (!empty($_GET['sid'])) { 
	$sid = sprintf("%d", $_GET['sid']); 
} else {
	$sid = 0;
}

$table = $Servers[$sid]['name'];
if (empty($table)) { die("Uh oh! Lost the database?"); }

$basequery = sprintf("SELECT alliance,sum(population),AVG(SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d))) as distance, MIN(SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d))), COUNT(id) 
	FROM `%s` WHERE aid!=0 and SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d)) < %d 
	GROUP BY aid 
	ORDER BY sum(population) DESC", $x, $x, $y, $y, $x, $x, $y, $y, $table, $x, $x, $y, $y, $maxd);

if (!empty($x) and !empty($y)) { $my_res = mysql_query($basequery) or die(mysql_error()); }


?>
<html>
<head>
<title>Grettir's Travian Tool Chest: Alliance Finder</title>
<link rel=stylesheet type="text/css" href="af.css">
<link rel=stylesheet type="text/css" href="../calc.css">
</head>
<body>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-1081252-3");
pageTracker._initData();
pageTracker._trackPageview();
</script>
<?php include("../topbanner.php"); ?>
<div style="float: left; margin-right: 50px; margin-top: 10px;">
<form method=get>
X <input class="coord" type=text name="x" value="<?php echo $_GET['x']; ?>">,
Y <input class="coord" type=text name="y" value="<?php echo $_GET['y']; ?>">
Server 
<select name="sid">
	<?php foreach ($Servers as $i=>$server) {
				if ($i == $sid) {
					printf("<option SELECTED value=\"%d\">%s</option>", $i, $server['name']);
				} else {
					printf("<option value=\"%d\">%s</option>", $i, $server['name']);
				}
			}
	?>
</select>
Distance
<select name="maxd">
	<option <?php if ($maxd == 7) { echo "SELECTED"; } ?> value="7">7 squares</option>
	<option <?php if ($maxd == 20) { echo "SELECTED"; } ?> value="20">20 squares</option>
	<option <?php if ($maxd == 30) { echo "SELECTED"; } ?> value="30">30 squares</option>
	<option <?php if ($maxd == 50) { echo "SELECTED"; } ?> value="50">50 squares</option>
	<option <?php if ($maxd == 100) { echo "SELECTED"; } ?> value="100">100 squares</option>
	<option <?php if ($maxd == 10000) { echo "SELECTED"; } ?> value="10000">(Entire map)</option>
</select>
<input type=submit value="Search">
</form>
<table class="results">
<tr class="rbg">
<td>Alliance</td>
<td>Towns</td>
<td>Pop</td>
<td>Avg Pop</td>
<td>Avg distance</td>
<td>Min distance</td>
</tr>
<?php	
	if (!empty($x) and !empty($y)) {
		while ($row = mysql_fetch_array($my_res)) {
			?><tr>
			<td><?php echo $row['0']; ?></td>
			<td align="center"><?php echo $row['4']; ?></td>
			<td align="center"><?php echo $row['1']; ?></td>
			<td align="center">
			<?
				$avgpop = $row['1']/$row['4'];
				printf("%01.0f", $avgpop);
			?>
			</td>
			<td align="right"><?php printf("%01.2f", $row['2']); ?></td>
			<td align="right"><?php printf("%01.2f", $row['3']); ?></td>
			</tr><?php
		}
	} else {
		?><tr><td colspan="6">No alliances found, entered the query yet?</td></tr><?php
	}


#echo "Query: $basequery <br>\n";
?>
</table>
</div>
<div style="float: left; width: 400px;">
		<h3>Alliance Finder</h3>
		This tool will list the largest alliance within X squares to your position.<br>
		<br>
		<strong>Possible uses include:</strong> Check if your alliance can back your new city up, find a new alliance, what alliance might be likely to attack you or to find out how much influence your alliance have in a certain area.
</div>
<br style="clear: left;">
</body>
</html>
