<?php 
$HTTP_REFERER = getenv('HTTP_REFERER');

include("config.php");
include("functions.php");
//include("../hh.scrolls.org/ldap.php");
session_start();

// Used for loggin purposes, identify variable
$Identity = sprintf("%s - user agent (%s) x(%s) y(%s) a(%s) p(%s)", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_GET['x'], $_GET['y'], $_GET['a'], $_GET['p']);
/*
if (in_blocklist($_SERVER['REMOTE_ADDR'],$BlockList)) {
	Header("Location: /blocked.html");
}
*/
if (!empty($_POST['login']['name']) AND user_verify($conn, $_POST['login']['name'], $_POST['login']['pass'])) {
	$_SESSION['username'] = $_POST['login']['name'];

	$ret = logit($LogFile, sprintf("%s logged in from %s", $_SESSION['username'], $_SERVER['REMOTE_ADDR']));
	if ($ret == -1) { printf("crap"); }
/*
	if (is_writable($LogFile)) {
    if (!$handle = fopen($LogFile, 'a')) {
         echo "Cannot open file ($filename)";
         exit;
    }
    if (fwrite($handle, sprintf("%s - %s logged in\n", date("d.m.Y - H:i:s"), $_SESSION['username'])) === FALSE) {
        echo "Cannot write to file ($filename)";
        exit;
    }
    fclose($handle);

	} else {
    echo "The file $filename is not writable";
	}
*/

	Header("Location: .".$_POST['referer']);
	//printf("login (%s) <br>", $_POST['login']['name']);
}

if (isset($_GET['logout'])) {
	unset($_SESSION['username']);
}

$sid = sprintf("%d", $_GET['sid']);
if (empty($sid)) { 
	$sid = 0;
}

$table = $Servers[$sid]['name'];
if (empty($table)) { die("Uh oh! Lost the database?"); }

$pDBhost = mysql_connect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");



function tribename($tid) {
	switch ($tid) {
		case 1:
			return "roman";
		break;
		case 2:
			return "teuton";
		break;
		case 3:
			return "gaul";
		break;
	}
}




?>
<html>
<head>
<?php if (!empty($_GET['x']) and !empty($_GET['y'])) { ?>
<title>RDC <?php printf("(%d|%d)", $_GET['x'], $_GET['y']); ?> - Travian Scrolls</title>
<?php } else { ?>
<title>RDC - Travian Scrolls</title>
<?php } ?>
<link rel=stylesheet type="text/css" href="calc.css"><!-- inside index -->
</head>
<body>
<?php include("form.php"); ?>
<br style="clear: both;"></br>
<?php

if ($sid == 99999) {
	$ret = logit($LogFile, sprintf("Requested login from %s", $Identity));
	//$Details = sprintf("Referer: %s\n\nUri: %s\n\nBrowser: %s\n\nIP: %s\n", $HTTP_REFERER, $_SERVER['REQUEST_URI'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
	//$Header = "From: RDC <travian@scrolls.org>";
	//mail("andri80@gmail.com", "RDC Referer attempt", $Details, $Header);
	?><div id="error_bar">
	<h3>Krigstid </h3>
	Kære RDC bruger,<br>
<br>
Jeg spiller i øjblikket på dk5 som <i>Andrioid</i> og jeg har hørt nogensteder at mine verktøjer bliver brugt imod migselv og andre i min alliance.<br>
<br>
Hvis du er en medlem i mit forbund (?), kan du log på med dit brugernavn.<br><br>
<strong>mvh,<br>
Andrioid / GrettirSterki</strong><br>
<br>
<form method=post>Brugernavn<br>
<input type="text" name="login[name]"><br>
<input type="hidden" name="referer" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
Kodeord<br>
<input type="password" name="login[pass]"><br>
<input type=submit></form><br>
	</div>
	<?php
} elseif (isset($_GET['x']) and isset($_GET['y']) and $_GET['x'] != NULL and $_GET['y'] != NULL) {
	if ($sid == 14) { logit($LogFile, sprintf("%s: %s", $_SESSION['username'], $Identity)); }
	$X = sprintf("%d", $_GET['x']);
	$Y = sprintf("%d", $_GET['y']);
	$tsq = sprintf("%d", $_GET['tsq']);
	$Syntax = "";
	if (!empty($_GET['a']) or !empty($_GET['size']) or !empty($_GET['p'])) { // Customized WHERE section
		if (!empty($_GET['a'])) {
			$Syntax .= a2syntax($_GET['a']);
		}
		//printf("syntax 1 (%s) <br>\n", $Syntax);
		if (!empty($_GET['size'])) {
			$Syntax .= s3syntax($_GET['size'], $Syntax); 
		}
		
		if (!empty($_GET['p'])) {
			$Syntax .= p2syntax($_GET['p'],$Syntax);
		}

		//printf("syntax 2 (%s) <br>\n", $Syntax);
		$Query = sprintf("SELECT village, alliance, player, population, SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d)) as distance, x, y, tid, uid, aid, vid FROM %s WHERE %s ORDER by distance LIMIT 0,200", $X, $X, $Y, $Y, $table, $Syntax);
	} else { // Normal query 
		$Query = sprintf("SELECT village, alliance, player, population, SQRT(ABS(x-%d)*ABS(x-%d)+ABS(y-%d)*ABS(y-%d)) as distance, x, y, tid, uid, aid, vid FROM %s ORDER by distance LIMIT 0,200", $X, $X, $Y, $Y, $table);	
	}
	//printf("Debug:  <strong>%s</strong> <br>\n", $Query);	
	$pQuery = mysql_query($Query) or die(mysql_error());
	if (mysql_num_rows($pQuery) > 0) {
		?>
		<span class="hugetext">Results</span><br>
		<div style="float: left;">
		<table class="results">
		<tr class="highlight">
		<td>Name<br><span class="tiny"><span class=roman>Romans</span>, <span class=teuton>Teutons</span>, <span class=gaul>Gauls</span></span></td><td>Alliance</td><td colspan=2>Village<br><span class="tiny">List, population and Travian links</span></td><td style="text-align: center;">X|Y</td><td>Distance</td>
				<?php if (!empty($_GET['speed'])) { ?>
					<td>Travel time</td>
				<?php } ?>
		</tr>
		<?php
		while(list($village, $alliance, $player, $population, $distance, $rx, $ry, $tid, $uid, $aid, $vid) = mysql_fetch_row($pQuery)) {
			//$link = sprintf("%s?x=%d&y=%d&sid=%d&a=%s&size=%d&speed=%d", $PHP_SELF, $rx, $ry, $sid, $_GET['a'], $_GET['size'], $_GET['speed']);
			$strpos = strrchr($Servers[$sid]['dumpfile'], '/');
			$temp_url = str_replace($strpos, "", $Servers[$sid]['dumpfile']);
			//$temp_url = substr($Servers[$sid]['dumpfile'], 0, 10);
			$link = sprintf("%s/karte.php?z=%s", $temp_url, xytoz($rx, $ry));
			$attlink = sprintf("%s/a2b.php?z=%s", $temp_url, xytoz($rx, $ry));
			$userlink = sprintf("%s/spieler.php?uid=%s", $temp_url, $uid);
			$aidlink = sprintf("%s/allianz.php?aid=%s", $temp_url, $aid);
			preg_match('/^(?P<server>[a-z0-9\.]+)(\/?)$/i', $Servers[$sid]['dumpfile'], $match);
			$maplink = sprintf("/maps/%s?x=%d&y=%d&zoomlevel=30", serverNameFix($Servers[$sid]['dumpfile']), $rx, $ry);
			// Village List (used for mapping)
			$VillageList[] = $vid;
			if (empty($rowcolor) or $rowcolor == "even") { 
				$rowcolor = "odd"; 
			} else {
				$rowcolor = "even";
			}
			?><tr class="<?php echo $rowcolor; ?>">
				<td><span class="<?php echo tribename($tid); ?>"><?php printf("<a title=\"%s\" href=\"%s\" target=\"_blank\">%s</span>", 'Ingame: User profile', $userlink, $player); ?></td>
				<td class="aline"><?php printf("<a title=\"%s\" href=\"%s\" target=\"_blank\">%s</a>&nbsp;", 'Ingame: Alliance profile', $aidlink, $alliance); ?></td>
				<td><?php printf("%s (%d)", $village, $population); ?></td>
				<td><a title="Dynamic Map" href="<?php echo $maplink; ?>"><img src="/img/map.png" border="0"></a>&nbsp;<a href="<?php echo $link; ?>" title="Ingame: map" target="_blank"><img src="/img/world.gif" border=0></a>&nbsp;<a href="<?php echo $attlink; ?>" title="Ingame: Send units" target="_blank"><img src="/img/att_all.gif" border=0></a></td>
				<td style="text-align: right;"><?php echo "(".$rx."|".$ry.")</td>"; ?></td>
				<td style="text-align: right;"><?php printf("%01.2f", $distance); ?></td>
				<?php if (!empty($_GET['speed'])) { ?>
					<td style="text-align: right;"><?php printf("%s", sec2counter(xy_sec(sprintf("%d", $_GET['speed']), $distance, $tsq))); ?></td>
				<?php } ?>
			</tr><?
			//printf("<tr><td>%s</td><td>%s</td><td><a href=\"%s\">%s</a></td><td>%s</td><td>%s (%d)</td> </tr>\n", $alliance, $player, $link, $village, $population, $distance, $tid);
			//printf("<tr><td colspan=5>mamma</td></tr>\n");
		}
		echo "</table></div>\n";

	for ($i = 0; $i<count($VillageList); $i++) {
		if ($i != count($VillageList)-1) { 
			$VillageStr .= sprintf("id:%d,", $VillageList[$i]); 
		}	else {
			$VillageStr .= sprintf("id:%d", $VillageList[$i]);
		}
	}
	$zoomlvl = 0.7;
	$MapURL = sprintf("http://travmap.shishnet.org/map.php?lang=da&country=America&server=%s&town=%s&groupby=alliance&colby=alliance&zoom=	%d,%d,%f0.1&casen=on&format=%s&caption=travian.scrolls.org", $ServerURL, $VillageStr, $_GET['x'],$_GET['y'], $zoomlvl, "png");
	$MapURLsvg = sprintf("http://travmap.shishnet.org/map.php?lang=da&country=America&server=%s&town=%s&groupby=alliance&colby=alliance&zoom=%d,%d,%f0.1&casen=on&format=%s&caption=travian.scrolls.org", $ServerURL, $VillageStr, $_GET['x'],$_GET['y'], $zoomlvl, "svg");

	printf("<div id=\"map_bar\">Map of your query:<br><a href=\"%s\">PNG</a> <a href=\"%s\">SVG</a><br>Courtesy of <a href=\"http://travmap.shishnet.org\">TravMap</a></div><br style=\"clear: both;\">\n", $MapURL, $MapURLsvg);
	}
} else {
	?>
	<div id="mid_bar">
	<strong>This program can list towns around you with relativity to distance and travel time.</strong><br>
		<br>
		<div style="float: left; width: 400px;">
			<strong>Possible uses include:</strong>
			<ul>
				<li><span style="color: red; font-weight: bold;">War:</span> All your enemies in range. Fakes, attacks, catapults?</li>
				<li><span style="color: darkgreen; font-weight: bold;">War:</span> All your allies in range. Reinforcements?</li>
				<li>Attack planning</li>
				<li>Catapulting times</li>
				<li>New city among friends?</li>
				<li>Farming lists</li>
			</ul>
		</div>
		<div style="float: left; width: 400px;">
			<strong>Examples</strong><br>
			<ul>
			<li><a href="<?php echo $PHP_SELF; ?>?x=-51&y=-53&sid=5&speed=7&size=&a=">How long it takes my clubswingers to reach its neighbors</a></li>
			<li><a href="<?php echo $PHP_SELF; ?>?x=-51&y=-53&sid=5&speed=3&size=400&a=">Catapult times to/from a town (limited to biggest neighbors)</a></li>
			<li><a href="<?php echo $PHP_SELF; ?>?x=-51&y=-53&sid=5&speed=0&size=&a=HH+C%2C+HH+SV">Some alliance</a></li>
			<li><a href="<?php echo $PHP_SELF; ?>?x=-51&y=-53&sid=5&speed=0&size=500&a=">Everyone bigger than 500 and their distance to a town</a></li>
		</div><br>
	</div>
<?php
}
?>
<br style="clear: both;"></br>
Tool created by Andri (<a href="http://andrioid.net">Andrioid</a>).<br>
<br>
Feedback welcome at <br><img src="img/gmail.png">

<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://scrolls.org/piwik/" : "http://scrolls.org/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 2);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://scrolls.org/piwik/piwik.php?idsite=2" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tag -->
</body>
</html>
