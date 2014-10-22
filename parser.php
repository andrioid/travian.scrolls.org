<?php

function ztoxy ($z) {
/*
x=-400+(d-1)%801
y=401-ceil(d/801)

*/
	$x = -400+($z-1)%801;
	$y = 401-ceil($z/801);

//	$y=ceil(400-$z/801);
//	$x=z-401-((400-$y)*801);
	return(array(
		$x,
		$y
	));
}

function extract_d ($link) {
		$f = strpos($link, "karte.php?d=")+strlen("karte.php?d=");
		$c = strpos($link, "&c=");
		$d = substr($link, $f, $c-$f);
		$cd = substr($link, $c+3, strlen($link)-($c+4));
		return array(
			$d,
			$cd
		);
		//printf("Link (%s) (%s) (%s)<br>", $f, $c, $d);
}

if (!empty($_POST['pasteregion'])) {
	$pasteregion = stripslashes($_POST['pasteregion']);
	#preg_match("/Forkortelse:\t(.+)\ \t/", $_POST['pasteregion'], $tag);
	#preg_match("/Navn:\t(.+)\n/", $_POST['pasteregion'], $alliance);
	$len = strlen($pasteregion);
	#$varray = explode("\n", $pasteregion);
	/* Find action element */
	//$pasteregion = "<table cellspacing=\"1\" cellpadding=\"2\" class=\"tbg\">mamma er foli </td></tr></table></td></table>";
	if ($matches = preg_split("~<(/?)([^>]*)>~", $pasteregion,-1,PREG_SPLIT_DELIM_CAPTURE)) {
		foreach ($matches as $i=>$value) {
			if ($value == "table cellspacing=\"1\" cellpadding=\"2\" class=\"tbg\"") {
				$mark = $i;
/*				$townlink = $matches[$i+9];
				$town = $matches[$i+13];
				$ownlink = $matches[$i+27];
				$description = $matches[$i+31];
				$timer = $matches[$i+244];
				$arrival = $matches[$i+253];
*/
				list($d,$c) = extract_d($matches[$i+9]);
				list($x,$y) = ztoxy($d);

				if (!empty($matches[$i+244]) and !empty($matches[$i+253]) and !empty($d)) {
					$Incoming[] = array(
						'village' => $matches[$i+13],
						'x' => $x,
						'y' => $y,
						'id' => $d,
						'c' => $c,
	//					'townlink' => $townlink,
						'description' => $matches[$i+31],
						'timer' => $matches[$i+244],
						'arrival' => $matches[$i+253]
					);
				}
				//printf("Incoming %d (%s) (%s)<br>\n", $i, $town, $townlink);
			} elseif (!empty($value)) {
				//printf("<pre>[%d] [%d] %s</pre>", $i, $i-$mark, htmlspecialchars($value));
			}

		}
		echo "<pre>"; print_r($Incoming); echo "</pre>\n";
	} else {
		printf("No luck<br><br>\n");
	}
	//printf("String (%s) <br>\n", htmlspecialchars($pasteregion));
	#$spos = strpos($pasteregion, "\tSpillere \t");
	#$spos = $spos+36;
	#$epos = strpos($pasteregion, "Landsbyer:");
	#$epos = $epos-2;
	#$plen = $epos-$spos;
	#$players = substr($pasteregion, $spos, $plen);
	#$parray = explode("\n", $players);
	#echo "(".$spos.")"i;
	#printf("(%d/%d/$d") (%s)", $spos, $epos, $plen, $players);
}

?>

<HTML>
<HEAD>
	<TITLE>Rally Point Parser</TITLE>
</HEAD>
<BODY>
<FORM METHOD=POST>
<TEXTAREA NAME="pasteregion"></TEXTAREA>
<INPUT TYPE=SUBMIT value="Submit">
</FORM>
<?PHP
/*
foreach ($parray as $p) {
	$e = explode("\t", $p);
	echo "Rank: ".$e[0]." Name: ".$e[1]." Pop: ".$e[2]." Towns: ".$e[3]." <br>\n";
}
*/
?>
<PRE>
<?php

print_r($xml);

?>
</PRE>
</BODY>
</HTML>

