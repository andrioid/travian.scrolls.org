<?php
	date_default_timezone_set("Europe/Copenhagen");

	$Report =	$_POST['report'];

	$Head = sprintf("Hej hunder!

Så går vi igang imod vores fjender og det er vigtigt at alle sender på rigtige tidspunkter og de som sender cleanere sender før, eller på den rigtige tidspunkt (ellers er vi fucked).

Hvis du ikke har mulighed at opfylde din opgave, skriv med det samme til mig.

Din opgaveliste:\n\n");
	$Foot = sprintf("\n\n* 1. angreb betyder at du skal ramme først
* 2. angreb betyder at du skal ramme efter første angreb, tiden på 2. angrebs har været ændret med +2 sek.
* Clean: fjern forsvar
* Waves: 3-5 bølger, brug samme antal af bølger til fakes og alvorlige angrebs
* Waves: I bestemmer selv, hvor mange tropper I sender med.
* Pultemål: kornavlere, kornkammer, lager, bageri, kornmølle
* Pulteantal: bestem selv :)

mvh,
Andrioid (angrebskoordinator)");

	function action ($id) {
		switch ($id) {
			case 1:
				return "Clean+pulter";
			break;
			case 2:
				return "Support+pulter";
			break;
			case 3:
				return "Fake clean+pulter";
			break;
			case 4:
				return "Fake support+pulter";
			break;
		}
	}

	//printf("count (%d) <br>\n", count($Report));
	foreach ($Report as $player=>$rline) {
		foreach ($rline as $key=>$aline) {
			if ($aline['action'] == 0) { 
				unset($Report[$player][$key]); 
			}
			if (!empty($aline['time'])) { $aTime = $aline['time']; $aTime2=$aTime+2; }
		}
	}

	/* And now for real */
	foreach ($Report as $player=>$rline) {
		if (count($rline) > 0) {
			/* Sort */
			usort($rline,"wm_sortattacks");
			/* Continue */
			printf("<strong>%s</strong><br>\n", base64_decode($player));
			printf("<textarea style=\"width: 800px; height: 200px; border: 0.5px solid #BCBCBC;\">");
			printf("%s\n\n", $_POST['head']);
			printf("[ Opgavelist ]\n");
			foreach ($rline as $key=>$aline) {
				if (!isset($UnitList[$aline['speed']])) { $Unitname = sprintf("Unitspeed(%d)", $aline['speed']); } else { $Unitname = $UnitList[$aline['speed']]; }
				if ($aline['action'] > 25 ) { $aline['sendat'] = $aline['sendat']+2; }
				//printf("[%s] %s\n - %s \"%s\" -- %s\n", date("d.m - H:i:s", $aline['sendat']), $aline['sxy'], $aline['txy'], $ActionList[$aline['action']], $aline['tplayer']);
				//printf("fra %s til %s send kl %s\n- %s: %s\n\n", $aline['sxy'], $aline['txy'], date("d.m - H:i:s", $aline['sendat']), $ActionList[$aline['action']], $aline['tplayer']);
				printf("fra %s sendes kl %s - %s\n", $aline['sxy'], date("d.m - H:i:s", $aline['sendat']), $ActionList[$aline['action']]);
				printf("til %s kl %s (%s)\n\n", $aline['txy'], date("d.m - H:i:s", $aline['endtime']), $aline['tplayer']);
			}
			//printf("1. angreb skal ramme %s og 2. angreb skal ramme %s den %s.\n", date("H:i:s", $aTime), date("H:i:s", $aTime2), date("d.m", $aTime));
			printf("\n%s", $_POST['foot']);
			printf("</textarea><br>\n");
		} else {
			printf("<strong>%s (unused)</strong><br>\n", base64_decode($player));
		}
	}
?>

<pre><?php print_r($_POST['report']); ?></pre>

