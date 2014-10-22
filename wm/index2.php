<?php 
	date_default_timezone_set("Europe/Copenhagen");
	include("../config.php");
	include("functions.php");

	$sid = sprintf("%d", $_POST['sid']);
	if (empty($sid)) { 
		$sid = 0;
	}

	$table = $Servers[$sid]['name'];
	if (empty($table)) { die("Uh oh! Lost the database?"); }

	$pDBhost = mysql_connect($DB['host'], $DB['user'], $DB['pass']);
	mysql_select_db("travian");
?>
<html>
<title>Travian War Machine</title>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<script type="text/javascript" src="tabber/tabber.js"></script>
<link rel="stylesheet" href="tabber/example.css" TYPE="text/css" MEDIA="screen">
<link rel="stylesheet" href="common.css" type="text/css" />
</head>
<body>
<form method="post">
<div id="source">
	<strong>Source (x,y,s,t,comment)</strong><br>
	<textarea name="source"><?php echo $_POST['source']; ?></textarea>
</div>
<div id="target">
	<strong>Target (x,y,offset,comment)</strong><br>
	<textarea name="target"><?php echo $_POST['target']; ?></textarea>
</div>
<div id="options">
	<strong>Max time</strong><br>
	<select name="maxtime" id="maxtime">
	<option></option>
		<?php
			for ($i=72; $i>=1; $i--) {
				if ($_POST['maxtime'] == $i*60*60) { $default = "SELECTED"; } else { $default = ""; }
				printf("<option value=\"%d\" %s>%dh</option>\n", $i*60*60, $default, $i);
			}
		?>
	</select><br>
	<strong>Server </strong>
	<select name="sid">
	<?php if (!$sid) { ?><option selected value="0">- none -</option><?php } ?>
	<?php foreach ($Servers as $i=>$server) {
				if ($i == $sid) {
					printf("<option SELECTED value=\"%d\">%s</option>", $i, $server['name']);
				} else {
					printf("<option value=\"%d\">%s</option>", $i, $server['name']);
				}
			}
	?>
	</select><br>
	<strong>Filter night-attacks</strong>
	&nbsp;<input type="checkbox" name="nights" value="1" <?php if ($_POST['nights'] == 1) { echo "CHECKED"; } ?>>
	<br>
	<strong>Planned time</strong><br>
	<select name="time[year]">
	<?php
		for ($i = 2008; $i<2020; $i++) {
			if ($_POST['time']['year'] == $i) { $select = "SELECTED"; } else { $select = ""; }
			printf("<option value=\"%d\" %s>%d</option>", $i, $select, $i);
		}
	?>
	</select>
	<select name="time[month]">
	<?php
		for ($i = 1; $i<13; $i++) {
			if ($_POST['time']['month'] == $i) { $select = "SELECTED"; } else { $select = ""; }
			printf("<option value=\"%d\" %s>%d</option>", $i, $select, $i);
		}
	?>
	</select>
	<select name="time[day]">
	<?php
		for ($i = 1; $i<32; $i++) {
			if ($_POST['time']['day'] == $i) { $select = "SELECTED"; } else { $select = ""; }
			printf("<option value=\"%d\" %s>%d</option>", $i, $select, $i);
		}
	?>
	</select><br>
	<select name="time[hour]">
	<?php
		for ($i = 0; $i<24; $i++) {
			if ($_POST['time']['hour'] == $i) { $select = "SELECTED "; } else { $select = ""; }
			printf("<option value=\"%d\" %s>%d</option>", $i, $select, $i);
		}
	?>
	</select>
	<select name="time[min]">
	<?php
		for ($i = 0; $i<=59; $i++) {
			if ($_POST['time']['min'] == $i) { $select = "SELECTED "; } else { $select = ""; }
			printf("<option value=\"%d\" %s>%d</option>", $i, $select, $i);
		}
	?>
	</select>
	<select name="time[sec]">
	<?php
		for ($i = 0; $i<=59; $i++) {
			if ($_POST['time']['sec'] == $i) { $select = "SELECTED "; } else { $select = ""; }
			printf("<option value=\"%d\" %s>%d</option>", $i, $select, $i);
		}
	?>
	</select>
	<br><br>
	<input type="submit" value="Find targets">
</div>
<div id="igm">
<strong>Head</strong><br>
<textarea name="head"><?php echo $_POST['head']; ?></textarea>
<br>
<strong>Foot</strong><br>
<textarea name="foot"><?php echo $_POST['foot']; ?></textarea>
</div>
<br style="clear: both;">
<?php
	if (!empty($_POST['source']) AND !empty($_POST['target'])) {
		foreach(explode("\n", $_POST['source']) as $line) {
			list($x, $y, $s, $t, $comment) = explode(",", $line);
			$x = trim($x);
			$y = trim($y);
			$s = trim($s);
			$t = trim($t);
			if (!$t) { $t=0; }
			$Source[] = array(
				'x' => $x, 
				'y' => $y, 
				's' => $s, 
				't' => $t, 
				'comment' => $comment,
			);
		}
		foreach(explode("\n", $_POST['target']) as $line) {
			list($x, $y, $offset, $comment) = explode(",", $line);
			$x = trim($x);
			$y = trim($y);
			$offset = trim($offset);
			$Target[] = array('x' => $x, 'y' => $y, 'player' => "none", 'alliance' => "none", 'comment' => $comment, 'offset' => $offset);
		}
		$time = mktime($_POST['time']['hour'], $_POST['time']['min'], $_POST['time']['sec'], $_POST['time']['month'], $_POST['time']['day'], $_POST['time']['year'], 0);
		//printf("unixtime (%s) (%s) (%s)<br>\n", $time, time(), date("d.m - H:i:s", $time));
		// Calculate distance
		for ($i = 0; $i < count($Source); $i++) {
			$XY = sprintf("%d,%d", $Source[$i]['x'], $Source[$i]['y']);
			$XY_list[$XY] = 0;
			for ($j = 0; $j < count($Target); $j++) {
				$XY = sprintf("%d,%d", $Target[$j]['x'], $Target[$j]['y']);
				$XY_list[$XY] = 0;
				$Target[$j]['s'][$i] = array(
					'distance' => xy_distance($Source[$i]['x'], $Target[$j]['x'], $Source[$i]['y'], $Target[$j]['y']), 
					'x' => $Source[$i]['x'], 
					'y' => $Source[$i]['y']
				);
			}
		}
		$Source = wm_addtargets($Source,$Target);
		$Source = wm_sendtime($Source,$time);
		$Source = wm_addnames($Source,$table);
		$Source = wm_maxtime($Source,$_POST['maxtime']);
		$Source = wm_nights($Source,$_POST['nights']);
		$Source = wm_actions($Source,$_POST['nreport']);
		$TA = wm_targetview($Source);

		/* Lets create a table */

		?>
<div class="tabber">
	<div class="tabbertab">
		<h3>Target Planning</h3>
		<table id="tartar">
		<tr id="top"><th>Player</th><th>Alliance</th><th>XY</th><th>Comment</th><th>Player</th><th>Alliance</th><th>XY</th><th>Comment</th><th>Traveltime</th><th>Distance</th><th>Send at</th><th>Action</th></tr>
		<?php
			$counter = 0;
			$rowcolor = 0;
			foreach ($TA as $i=>$tline) {
				if (isset($tline['sources'])) {
					foreach ($tline['sources'] as $j=>$sline) {
						$counter++;
						if ($tmask != $tline['x'].$tline['y'] and isset($rowcolor)) { $rowcolor++; }
						if ($rowcolor % 2) {	$rowclass = "row1"; } else { $rowclass = "row2"; }
						$key = base64_encode($sline['player']);
						$tmask = $tline['x'].",".$tline['y'];
						$smask = $sline['x'].",".$sline['y'];
						$nkey = base64_encode($tmask.",".$smask);
						$action = sprintf("<select id=\"inselect\" name=\"report[$key][$counter][action]\">%s</select>", optlist($ActionList, $_POST['report'][$key][$counter]['action']));
						$naction = sprintf("<select id=\"inselect\" name=\"nreport[$j][$i][action]\">%s</select>", optlist($ActionList, $_POST['nreport'][$nkey]['action']));
						printf("<tr id=\"$nkey\" class=\"$rowclass\"><td id=\"nametd\">%s</td><td id=\"allytd\">%s</td><td id=\"xytd\">%s</td><td id=\"comment\">%s</td><td id=\"nametd\">%s</td><td id=\"allytd\">%s</td><td id=\"xytd\">%s</td><td id=\"comment\">%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", 
							sprintf("%s", utf8_encode($sline['player'])),
							sprintf("%s", utf8_encode($sline['alliance'])),
							sprintf("%d|%d", $sline['x'], $sline['y']),
							$sline['comment'],
							sprintf("%s", utf8_encode($tline['player'])), 
							sprintf("%s", utf8_encode($tline['alliance'])),
							sprintf("%d|%d", $tline['x'], $tline['y']),
							$tline['comment'],
							sec2counter($sline['sec']),
							sprintf("%0.2f", $sline['dist']),
							date("Y-m-d H:i:s", $sline['sendtime']),
							$naction
						);

						printf("
							<input type=hidden name=report[$key][$counter][splayer] value=\"%s\">
							<input type=hidden name=report[$key][$counter][sxy] value=\"%s\">
							<input type=hidden name=report[$key][$counter][comment] value=\"%s\">
							<input type=hidden name=report[$key][$counter][tplayer] value=\"%s\">
							<input type=hidden name=report[$key][$counter][txy] value=\"%s\">
							<input type=hidden name=report[$key][$counter][sec] value=\"%s\">
							<input type=hidden name=report[$key][$counter][dist] value=\"%s\">
							<input type=hidden name=report[$key][$counter][speed] value=\"%s\">
							<input type=hidden name=report[$key][$counter][sendat] value=\"%s\"> 
							<input type=hidden name=report[$key][$counter][endtime] value=\"%s\">", 
							sprintf("%s [%s]", utf8_encode($sline['player']), utf8_encode($sline['alliance'])),
							sprintf("(%d|%d)", $sline['x'], $sline['y']),
							trim($sline['comment']),
							sprintf("%s [%s]", utf8_encode($tline['player']), utf8_encode($tline['alliance'])), 
							sprintf("(%d|%d)", $tline['x'], $tline['y']),
							sec2counter($sline['sec']),
							sprintf("%0.2f", $sline['dist']),
							$sline['s'],
							$sline['sendtime'],
							$sline['endtime'],
							$action
						);
					}
				}
			}
		?>
		</table>
		<input type=submit>
	</div>
<?php 
	$rp = 0;
	if (count($_POST['report']) > 0) {
		foreach ($_POST['report'] as $i => $rline) {
			foreach ($rline as $j => $aline) {
				if (!empty($aline['action'])) { $rp++; }
			}
		}
	}

	if ($rp > 0) { ?>
	<div class="tabbertab">
	<h3>Message Templates</h3>
	<?php include("report.php"); ?>
	</div>
<?php } ?>
		<?php


		/* Debug stuff */
		echo "<div class=\"tabbertab\">";
		echo "<h3>Debug</h3>\n";
		echo "<h3>Source</h3>";
		echo "<pre>"; print_r($Source); echo "</pre>\n";
		echo "<h3>Target</h3>";
		echo "<pre>"; print_r($TA); echo "</pre>\n";
		echo "</div>\n";

//		echo "<pre>"; print_r($Source2); echo "</pre>\n";
	}
?>
</div>
		</form>

