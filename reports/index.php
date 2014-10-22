<?php
mb_internal_encoding( 'UTF-8' );
include("/home/andri/travian.scrolls.org/config.php");
include("/home/andri/hh.scrolls.org/units.conf.php");

$pDBhost = mysql_pconnect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");

function name2tid ($name) {
	//$name = utf8_decode($name);
	//printf("%s - %s - %s<br>\n", $name, utf8_encode($name), utf8_decode($name));
	switch ($name) {
		case "Legionær":
			return 1;
		break;
		case "Køllesvinger":
			return 2;
		break;
		case "Falanks":
			return 3;
		break;
		case "Rotte":
			return 4;
		break;
		default:
			return -1;
		break;
	}
}

function format_number ($input) {
	if ($input > 0) {
		$Return = sprintf("<span style=\"color: red\">-%d</span>", $input);
	} else {
		$Return = sprintf("%d", $input);
	}
	return $Return;
}

function display_report ($Data,$id) {
	global $Races;
	$Return .= sprintf("<html><head><title>%s angriber %s</title>\n", $Data['a_player'], $Data['d_player']);
	$Return .= sprintf("<link rel=stylesheet type=\"text/css\" href=\"../reports.css\">\n");
	$Return .= sprintf("<h3>%s angriber %s den %s klokken %s</h3>\n", $Data['a_player'], $Data['d_player'], date("d.m.Y", $Data['time']), date("H:i:s", $Data['time']));
	$Return .= sprintf("<input style=\"width: 400px;\" type=text value=\"%s\"><br><br>\n", "http://travian.scrolls.org/d/".$id.".html");
	$Return .= sprintf("<table>\n");
	$Return .= sprintf("<tr><td colspan=3><b>%s</b><br>%s</td><td colspan=3><b>%s</b><br>%s</td></tr>\n", $Data['a_player'], $Data['a_village'], $Data['d_player'], $Data['d_village']);
	for ($i = 1; $i<11; $i++) {
		$aunit = $Data['a_units'][$i];
		$d1tid = $Data['d_units'][0]['tid'];
		$atid = $Data['a_units']['tid'];
		$Return .= sprintf("<tr><td><img src=\"%s\"></td><td id=\"units\">%d</td><td id=\"units\">%s</td><td><img src=\"%s\"></td><td id=\"units\">%d</td><td id=\"units\">%s</td></tr>",$Races[$atid]['units'][$i]['image'], $Data['a_units'][$i], format_number($Data['a_loss'][$i]), $Races[$d1tid]['units'][$i]['image'], $Data['d_units'][0][$i], format_number($Data['d_loss'][0][$i]));
	}
	$Return .= sprintf("<tr><td><img src=\"%s\"></td><td id=\"units\">%d</td><td id=\"units\">%s</td><td><img src=\"%s\"></td><td id=\"units\">%d</td><td id=\"units\">%s</td></tr>","/img/units/hero.gif", $Data['a_units'][11], format_number($Data['a_loss'][11]), "/img/units/hero.gif", $Data['d_units'][0][11], format_number($Data['d_loss'][0][11]));
	$Return .= sprintf("</table>");
	$Return .= sprintf("<br><strong>Bytte</strong><br>\n");
	$Return .= sprintf("<img src=\"/img/r/1.gif\"> %d <img src=\"/img/r/2.gif\"> %d <img src=\"/img/r/3.gif\"> %d <img src=\"/img/r/4.gif\"> %d <br>\n", $Data['spoils'][0], $Data['spoils'][1], $Data['spoils'][2], $Data['spoils'][3]);
	return $Return;
}


if (!empty($_POST['report'])) {
	$Report = explode("\n", $_POST['report']);
	foreach ($Report as $line=>$eachline) {
		#fra\ landsbyen\ (?<village>\w+)
		#(?<name>\w+) fra
		if (preg_match("/^Sendt\:\ \tden\ (.*)\ klokken\ (.*)$/", $eachline, $matches)) {
			list($day,$month,$year) = explode(".", $matches[1]);
			list($hours,$min,$sec) = explode(":", $matches[2]);
			$Data['time'] = mktime($hours,$min, $sec, $month, $year);
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
			$Debug[] = sprintf("-> Date: %s - Time: %s\n", $matches[1], $matches[2]);
			$Debug[] = sprintf("Date %s\n", date("d.m.Y - H:i:s", $Data['time']));
		} elseif (preg_match("/^Angriber\ \t(.*)\ fra\ landsbyen\ (.*)$/", $eachline, $matches)) {
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
			$Debug[] = sprintf("-> Attacker: %s - Village: %s \n", $matches[1], trim($matches[2]));
			$Data['a_player'] = trim($matches[1]);
			$Data['a_village'] = trim($matches[2]);
			$context = 0; // Context: Attack
			//print_r($matches);
		} elseif (preg_match("/^Forsvarer\ \t(.*)\ fra\ landsbyen\ (.*)$/", $eachline, $matches)) {
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
			$Data['d_player'] = trim($matches[1]);
			$Data['d_village'] = trim($matches[2]);
			$context = 1; // Context: Defense
			//print_r($matches);
		} elseif (preg_match("/^\ \t\[/", $eachline, $matches)) {
			// Unit header
			$Units = explode(" \t", $eachline);
			$unit = str_replace(array('[', ']'), "", $Units[1]);
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
			$Debug[] = sprintf("-> Unit: \"%s\"\n", $unit);
		} elseif (preg_match("/^Enheder\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)(\t(\d+))?/", $eachline, $matches)) {
			unset($matches[0]);
			$matches['tid'] = name2tid($unit);
			if ($context == 0) {
				$Data['a_units'] = $matches;
			} elseif ($context == 1) {
				$Data['d_units'][] = $matches;		
			}
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
			//echo "<pre>"; print_r($matches); echo "</pre>";
		} elseif (preg_match("/^Tab\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)(\t(\d+))?/", $eachline, $matches)) {
			unset($matches[0]);
			$matches['tid'] = name2tid($unit);
			if ($context == 0) {
				$Data['a_loss'] = $matches;
			} elseif ($context == 1) {
				$Data['d_loss'][] = $matches;		
			}
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
			//echo "<pre>"; print_r($matches); echo "</pre>";
			
		} elseif (preg_match("/^Bytte\ \t(\d+)\ (\d+)\ (\d+)\ (\d+)/", $eachline, $matches)) {
			$Data['spoils'][0] = $matches[1];
			$Data['spoils'][1] = $matches[2];
			$Data['spoils'][2] = $matches[3];
			$Data['spoils'][3] = $matches[4];
			$Debug[] = sprintf("Match (%d)\t: %s", $line, $eachline);
		} else {
			$Debug[] = sprintf("No match (%d)\t: %s", $line, $eachline);
		}
	}
	// Time to write to database and redirect
	$Query = sprintf("INSERT INTO reports SET atime=%d, aplayer='%s', dplayer='%s', avillage='%s', dvillage='%s', s1=%d, s2=%d, s3=%d, s4=%d", $Data['time'], $Data['a_player'], $Data['d_player'], $Data['a_village'], $Data['d_village'], $Data['spoils'][0], $Data['spoils'][1], $Data['spoils'][2], $Data['spoils'][3]);
	$Result = mysql_query($Query) or die(mysql_error());
	$report_id = mysql_insert_id($pDBhost);
	$Query2 = sprintf("INSERT INTO reports_t SET rid=%d, tid=%d, type=%d, t1=%d, t2=%d, t3=%d, t4=%d, t5=%d, t6=%d, t7=%d, t8=%d, t9=%d, t10=%d, t11=%d, l1=%d, l2=%d, l3=%d, l4=%d, l5=%d, l6=%d, l7=%d, l8=%d, l9=%d, l10=%d, l11=%d",
	$report_id, $Data['a_units']['tid'], 0, $Data['a_units'][1], $Data['a_units'][2], $Data['a_units'][3], $Data['a_units'][4], $Data['a_units'][5], $Data['a_units'][6], $Data['a_units'][7], $Data['a_units'][8], $Data['a_units'][9], $Data['a_units'][10], $Data['a_units'][11],	$Data['a_loss'][1], $Data['a_loss'][2], $Data['a_loss'][3], $Data['a_loss'][4], $Data['a_loss'][5], $Data['a_loss'][6], $Data['a_loss'][7], $Data['a_loss'][8], $Data['a_loss'][9], $Data['a_loss'][10], $Data['a_loss'][11]
	);
	$Result = mysql_query($Query2) or die(mysql_error());

	$dcount = count($Data['d_units']);
	for ($i = 0; $i<$dcount; $i++) {
		$Query3 = sprintf("INSERT INTO reports_t SET rid=%d, tid=%d, type=%d, t1=%d, t2=%d, t3=%d, t4=%d, t5=%d, t6=%d, t7=%d, t8=%d, t9=%d, t10=%d, t11=%d, l1=%d, l2=%d, l3=%d, l4=%d, l5=%d, l6=%d, l7=%d, l8=%d, l9=%d, l10=%d, l11=%d",
	$report_id, $Data['d_units'][$i]['tid'], 1, $Data['d_units'][$i][1], $Data['d_units'][$i][2], $Data['d_units'][$i][3], $Data['d_units'][$i][4], $Data['d_units'][$i][5], $Data['d_units'][$i][6], $Data['d_units'][$i][7], $Data['d_units'][$i][8], $Data['d_units'][$i][9], $Data['d_units'][$i][10], $Data['d_units'][$i][11],	$Data['d_loss'][$i][1], $Data['d_loss'][$i][2], $Data['d_loss'][$i][3], $Data['d_loss'][$i][4], $Data['d_loss'][$i][5], $Data['d_loss'][$i][6], $Data['d_loss'][$i][7], $Data['d_loss'][$i][8], $Data['d_loss'][$i][9], $Data['d_loss'][$i][10], $Data['d_loss'][$i][11]
		);
		//printf("Query: %s <br>\n", $Query3);
		$Result = mysql_query($Query3) or die(mysql_error());
	}

	$id = $Data['time'].$report_id;
	$baseurl = "http://travian.scrolls.org/reports/";
	$filename = "d/".$id.".html";
	$url = $baseurl.$filename;
	$fp = fopen($filename, "w");
	fwrite($fp, display_report($Data, $id));
	fclose($fp);
	Header("Location: $url");

}



?>
<html>
<head>
<title>Travian Scrolls</title>
<link rel=stylesheet type="text/css" href="../calc.css">
</head>
<body>
<?php include("../topbanner.php"); ?>
<br>
<form method="post">
<textarea style="width: 400px; height: 200px;" name="report"></textarea><br>
<input type=submit>
</form>
<?php //echo display_report($Data, $id); ?>
<br>
<div style="border: 1px solid #BBBBBB; width: 90%; padding-left: 10px">
<pre>
<?php //print_r($Data); ?>
<?php //foreach($Debug as $debugline) {
	//printf("%s", $debugline);
//}
?></pre>
</div>

</body>
</html>
