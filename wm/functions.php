<?php	// Functions

function xy_distance ($x1, $x2, $y1, $y2) {
	$x = abs($x1-$x2);
	if ($x > 400) {
		$x = abs($x-(400*2+1));
	}
	$y = abs($y1-$y2);
	if ($y > 400) {
		$y = abs($x-(400*2+1));
	}
	$return = sqrt(($x*$x) + ($y*$y));
	return $return;
}

function xy_sec ($sqph=3, $distance, $t=0) {
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

function traveltime ($sqph, $distance, $t=0) {
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

function xy_where ($list) {
	$return = "";
	$i = 0;
	foreach ($list as $line=>$bleh) {
		list($x,$y) = explode(",", $line);
		if ($i != 0) {
			$return .= "OR ";
		}
		$return .= sprintf("(x=%d AND y=%d) ", $x, $y);
		$i++;
	}
	//printf("debug (%s)<br>\n", $return);
	return $return;
}

function sort_distance ($a, $b) {
	if ($a['distance'] == $b['distance']) { return 0; }
	if ($a['distance'] < $b['distance']) {
		return -1;
	} else {
		return 1;
	}
}

function wm_addtargets ($array,$targets) {
		if (count($array) < 1 or count($targets) < 1) { return 0; }
		foreach ($array as $arraynum=>$arrayline) {
			foreach ($targets as $targetnum=>$targetline) {
				$dist = xy_distance($array[$arraynum]['x'], $targetline['x'], $array[$arraynum]['y'], $targetline['y']);
				$array[$arraynum]['targets'][] = array(
					'x' => $targetline['x'], 
					'y' => $targetline['y'],
					'comment' => $targetline['comment'],
					'offset' => $targetline['offset'],
					'dist' => $dist,
					'sec' => sprintf("%d", xy_sec($array[$arraynum]['s'], $dist, $array[$arraynum]['t'])),
				);
			}
		}
		return $array;
}

function wm_sendtime ($array,$unixtime) {
		foreach ($array as $i=>$iline) {
			if (isset($iline['targets'])) {
				foreach ($iline['targets'] as $j=>$tline) {
					// We'll add the offset to the planned time here
					$otime = $unixtime+$array[$i]['targets'][$j]['offset'];
					$array[$i]['targets'][$j]['sendtime'] = sprintf("%d", $otime-$array[$i]['targets'][$j]['sec']);
					//$array[$i]['targets'][$j]['plantime'] = $unixtime;
					$array[$i]['targets'][$j]['endtime'] = $otime;
					//$array[$i]['targets'][$j]['land'] = date("H:i:s", $otime);
					//$array[$i]['targets'][$j]['send'] = date("H:i:s", $array[$i]['targets'][$j]['sendtime']);
				}
			}
		}
		return $array;
}

function wm_targetview ($array) {
		foreach ($array as $i=>$iline) {
			if (isset($iline['targets'])) {
				foreach ($iline['targets'] as $j=>$tline) {
					$tkey = sprintf("%d|%d", $tline['x'], $tline['y']);
					$Return[$tkey]['x'] = $tline['x'];
					$Return[$tkey]['y'] = $tline['y'];
					$Return[$tkey]['comment'] = $tline['comment'];
					$Return[$tkey]['player'] = $tline['player'];
					$Return[$tkey]['alliance'] = $tline['alliance'];
					$Return[$tkey]['sources'][] = array(
						'x' => $iline['x'],
						'y' => $iline['y'],
						's' => $iline['s'],
						't' => $iline['t'],
						'comment' => $iline['comment'],
						'dist' => $tline['dist'],
						'sendtime' => $tline['sendtime'],
						'endtime' => $tline['endtime'],
						'sec' => $tline['sec'],
						'player' => $iline['player'],
						'alliance' => $iline['alliance'],
//						'offset' => $tline['offset'],
					);
				}
			}
		}
		return $Return;
}

function wm_addnames ($array,$table) {
		$XY_list = array();
		foreach ($array as $i=>$iline) {
			$xy = sprintf("%d,%d", $array[$i]['x'], $array[$i]['y']);
			$XY_list[$xy] = 1;
			if (isset($iline['targets'])) {
				foreach ($iline['targets'] as $j=>$tline) {
					$xy = sprintf("%d,%d", $array[$i]['targets'][$j]['x'], $array[$i]['targets'][$j]['y']);
					$XY_list[$xy] = 1;
				}
			}
		}
		// Query player names, if set
		$xy_query = sprintf("SELECT player, alliance, x, y FROM %s WHERE %s", $table, xy_where($XY_list));
		//printf("query (%s)<br>\n", $xy_query);
		$result = mysql_query($xy_query) or die(mysql_error());
		while (list($player, $alliance, $x, $y) = mysql_fetch_row($result)) {
			$player = utf8_encode($player);
			foreach ($array as $i=>$line) {
				if ($line['x'] == $x AND $line['y'] == $y) {
					$array[$i]['player'] = $player;
					$array[$i]['alliance'] = $alliance;
				}
				if (isset($iline['targets'])) {
					foreach ($iline['targets'] as $j=>$tline) {
						if ($tline['x'] == $x AND $tline['y'] == $y) {
							$array[$i]['targets'][$j]['player'] = $player;
							$array[$i]['targets'][$j]['alliance'] = $alliance;
						}		
					}
				}
					//printf ("Match on $player <br>\n");
			}
		}
		return $array;
}

function wm_maxtime($array,$maxtime) {
	if (empty($maxtime)) { return $array; }
	foreach ($array as $i=>$line) {
		if (count($line['targets'] > 0)) {
			foreach ($line['targets'] as $j=>$tline) {
				//printf("(%s) < (%s) <br>\n", $tline['sec'], $maxtime);
				if ($tline['sec'] > $maxtime) {
					
					unset($array[$i]['targets'][$j]);
				}
			}
		}
	}
	return $array;
}

function wm_nights($array,$night) {
	if (empty($night) or $night != "1") { return $array; }
	foreach ($array as $i=>$line) {
		if (count($line['targets'] > 0)) {
			foreach ($line['targets'] as $j=>$tline) {
				//printf("(%s) < (%s) <br>\n", $tline['sec'], $maxtime);
				if (date("H", $tline['sendtime']) > 0 AND date("H", $tline['sendtime']) < 8) {
					unset($array[$i]['targets'][$j]);
				}
			}
		}
	}
	return $array;
}

function wm_actions($array,$action) {
	echo "<pre>"; 	print_r($action); echo "</pre>";
	
	if (empty($array) or empty($action)) { return $array; }
	foreach ($action as $sid=>$sline) {
		foreach ($sline as $tid=>$tline) {
			if (!empty($tline['action'])) {
				printf("sid (%s) tid (%s) action (%s) <br>\n", $sid, $tid, $tline['action']);
			}
		}
	}
	$return = $array;
	return $return;
}

function wm_sortattacks ($a, $b) {
    if ($a['sendat'] == $b['sendat']) {
        return 0;
    }
    return ($a['sendat'] < $b['sendat']) ? -1 : 1;
}

$ActionList[-1] = "-1 sec";
$ActionList[1] = "+1 sec";
$ActionList[2] = "+2 sec";
$ActionList[3] = "+3 sec";
$ActionList[4] = "1. angreb: Fake Waves";
$ActionList[26] = "2. angreb: Waves";
$ActionList[27] = "2. angreb: Fake Waves";

$UnitList[3] = "catapulter";


function optlist ($ActionList, $current=0) {
	$ActOpt = "<option></option>";
	foreach ($ActionList as $i => $action) {
		//printf("(%s) (%s) <br>\n", $ActionList[$current], $ActionList[$i]);
		if ($current == $i) { $selected = "SELECTED"; } else { $selected = ""; }
		$ActOpt .= sprintf("<option %s value=\"$i\">$action</option>", $selected);
	}
	return $ActOpt;
}

function GetCodeFromID($id){
  $rotate = (($id & 0x1FF) << 23 ) + (($id & 0xFFFFFE00) >> 9);
  if ($rotate < 0){
    return str_pad($rotate + 4294967296,10,'0',STR_PAD_LEFT);
  } else {
    return str_pad($rotate,10,'0',STR_PAD_LEFT);
  }
}

function GetIDFromCode($code){
  $number = (float)($code);
  $number= (int)($number);
  if($number < 0){
    return (($number & 0x7F800000) >> 23 ) + (($number & 0x7FFFFF) << 9) + 256;
  } else {
    return (($number & 0xFF800000) >> 23 ) + (($number & 0x7FFFFF) << 9);
  }
}

?>

