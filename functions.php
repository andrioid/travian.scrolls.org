<?php

function a2syntax ($a) {
	global $_GET;
	$syntax = "(";
	if (!empty($a)) {
		$a_split = split(",", $a);
		$a_total = count($a_split)-1;
		for ($i = 0; $i <= $a_total; $i++) {
			if (strstr($a_split[$i], "id:")) {
				$AllyId = substr(trim($a_split[$i]), 3, strlen($a_split[$i])-3);
				if ($i == $a_total) {
					$syntax .= sprintf("aid = '%d'", $AllyId);
				} else {
					$syntax .= sprintf("aid = '%d' OR ", $AllyId);
				}
				//printf("Ally id: %d <br>\n", $AllyId);
			} else {
				$a_split[$i] = addslashes(trim($a_split[$i]));
				if ($i == $a_total) {
					$syntax .= sprintf("alliance = '%s'", $a_split[$i]);
				} else {
					$syntax .= sprintf("alliance = '%s' OR ", $a_split[$i]);
				}
				//echo "a: $a_split[$i] ($i / $a_total)<br>\n";
			}
		}
		$syntax .= ")";
		return $syntax;
	} else {
		$syntax = "aid=0";
		return $syntax;
	}
}
function s2syntax($s,$Syntax) {
	global $_GET;
	if (empty($s)) { return; }
	if (empty($Syntax)) {
		$t = sprintf("population >= %d", $s);
	} else {
		$t = sprintf(" OR population >= %d", $s);
	}
	return $t; 
}
function s3syntax($s,$Syntax) {
	global $_GET;
	if (empty($s)) { return; }
	if (empty($Syntax)) {
		$t = sprintf("population >= %d", $s);
	} else {
		$t = sprintf(" AND population >= %d", $s);
	}
	return $t; 
}

function p2syntax ($a,$syn) {
	global $_GET;
	if (empty($syn)) { $syntax = "("; } else { $syntax = $oldSyntax." OR ("; }
	if (!empty($a)) {
		$a_split = split(",", $a);
		$a_total = count($a_split)-1;
		for ($i = 0; $i <= $a_total; $i++) {
			if (strstr($a_split[$i], "id:")) {
				$AllyId = substr(trim($a_split[$i]), 3, strlen($a_split[$i])-3);
				if ($i == $a_total) {
					$syntax .= sprintf("uid = '%d'", $AllyId);
				} else {
					$syntax .= sprintf("uid = '%d' OR ", $AllyId);
				}
				//printf("Ally id: %d <br>\n", $AllyId);
			} else {
				$a_split[$i] = addslashes(trim($a_split[$i]));
				if ($i == $a_total) {
					$syntax .= sprintf("player = '%s'", $a_split[$i]);
				} else {
					$syntax .= sprintf("player = '%s' OR ", $a_split[$i]);
				}
				//echo "a: $a_split[$i] ($i / $a_total)<br>\n";
			}
		}
		$syntax .= ")";
		return $syntax;
	} else {
		//$syntax = "1";
		return NULL;
	}
}

function xy_sec ($sqph=3, $distance, $t=0) {
	//printf("tsq = %s <br>\n", $t);
	if ($t==0 or $distance<=30) { 
		$tt = 1; 
	} else {
		$tt = 1+$t/10;
	}
	$uspeed = $sqph * $tt;
	$f30time = 30 * 3600/$sqph;	// 30 squares in seconds (instead of hours) devided by speed of unit
	$rtime = ($distance-30) * 3600/$uspeed;
/*
	printf("%f / (%f * %f) <br>\n", $distance, $sqph, $tt);
	$th = $rest / ($sqph * $tt);
	$ts = $th * 60 * 60;
	return $ts;
*/
	return $f30time+$rtime;
}

function sec2counter ($ts) {
	$periods = array(
		'hours' => 3600,
		'minutes' => 60,
		'seconds' => 1
	);

	foreach ($periods as $period=>$period_sec) {
		if ($ts >= $period_sec) {
			$duration[$period] = sprintf("%d", floor($ts / $period_sec));
			$ts -= $duration[$period] * $period_sec;
		} else {
			$duration[$period] = 0;
		}
	}
	//echo "th: $th ts: $ts \n";
	return sprintf("%02d:%02d:%02d", $duration['hours'], $duration['minutes'], $duration['seconds']);
}

function traveltime ($sqph, $distance) {
	$periods = array(
		'hours' => 3600,
		'minutes' => 60,
		'seconds' => 1
	);
	# Distance divided by unit speed
	$th = $distance / $sqph;
	# Into seconds
	$ts = $th * 60 * 60;
	
	foreach ($periods as $period=>$period_sec) {
		if ($ts >= $period_sec) {
			$duration[$period] = sprintf("%02.0f", floor($ts / $period_sec));
			$ts -= $duration[$period] * $period_sec;
		} else {
			$duration[$period] = 0;
		}
	}
	//echo "th: $th ts: $ts \n";
	return $duration['hours'].":".$duration['minutes'].":".$duration['seconds']."\n";
}
function time_to_sec ($timestring) {
	$t = explode(":", $timestring);
	$sec = ($t[0]*3600+$t[1]*60+$t[2]);
	return $sec;
}
function time_to_iso ($in, $st) {	/* incoming counter, server time */
	$in_s = time_to_sec($in);
	$st_s = time_to_sec($st);
	$dt_s = $in_s+$st_s;
	$days_to_go = gmdate("z", $dt_s);
	$time = gmdate("H:i:s", $dt_s);
	if ($days_to_go == 0) {
		$date = date("Y-m-d", strtotime("today"));
	} else {
		$date = date("Y-m-d", strtotime($days_to_go." day"));
	}
	$return = $date." ".$time;
	#printf("Date debug - Timer(%s) - Servertime (%s) - Result(%s) <br>\n", $in, $st, $return);
	return $return;
}

function allow_referer ($referer, $allowlist) {
	//return 1;
	if (empty($referer)) { return 1; }
	preg_match('@^(?:http://)?([^/]+)@i', $referer, $TEMP);
	$REFHOST = $TEMP[0];
	preg_match('/[^.]+\.[^.]+$/', $REFHOST, $TEMP);
	$REFHOST = $TEMP[0];
	if (in_array($REFHOST,$allowlist)) { return 1; } else { return 0; }
}

function in_blocklist ($host, $blocklist) {
	if (in_array($host,$blocklist)) { return 1; } else { return 0; }
}

function xytoz ($x, $y) {
	$return = ($x + 401) + ((400 - $y) * 801);
	return $return;
}

function ztoxy ($z) {
	$y=ceil(400-$z/801);
	$x=z-401-((400-$y)*801);
	return(array(
		'x' => $x,
		'y' => $z
	));
}

function serverNameFix ($dumpname) {
	if (preg_match('/^http:\/\/(?P<server>[a-z.0-9]+)/', $dumpname, $match)) {
		return $match['server'];
	}
	return false;
}


function betterServerlist($Servers) {
	$return = array();
	function cmp($a, $b) {
		$ab = strcmp($a['cc'], $b['cc']);
		if ($ab == 0) {
			return strcmp($a['short'], $b['short']);
		}
    return $ab;
	}

	foreach ($Servers as $sid=>$s) {
		$name = serverNameFix($s['dumpfile']);
		if (!preg_match('/\.(\w+)$/i', $name, $cc)) { die("Cannot match cc for ".$name); }
		if (!preg_match('/^(\w+)\./i', $name, $short)) { die("Cannot match short"); }
		$return[] = array(
			'name' => $name,
			'cc' => $cc[1],
			'short' => $short[1],
			'table' => $s['name'],
			'sid' => $sid
		);
	}
	usort($return, 'cmp');
	return $return;
}

function srt_sid ($a, $b) {
    return strcmp($a['orgname'], $b['orgname']);
}

function in_whitelist ($id, $WhiteList) {
	if (isset($WhiteList[$id])) { return 1; } else { return 0; }
}

function logit ($file, $message) {
	// Lets give the message a timestamp and a linebreak
	$message = sprintf("%s - %s\n", date("d.m.Y - H:i:s"), $message);
	if (is_writable($file)) {
    if (!$handle = fopen($file, 'a')) {
			printf("unable to open file for writing");
			return -1;
    }
    if (fwrite($handle, $message) === FALSE) {
				printf("unable to write");
        return -1;
    }
    fclose($handle);

	} else {
		printf("file is not writable");
    return -1;
	}
}

?>
