
<?php
	foreach ($Servers as $i=>$each_server) {
		list($tmp_country, $tmp_name) = explode('_', $each_server['name']);
		$ServerList[] = array(
			'name' => $tmp_name,
			'orgname' => $each_server['name'],
			'dumpfile' => $each_server['dumpfile'],
			'sid' => $i,
			'country' => $tmp_country,
		);
	}
	usort($ServerList, "srt_sid");
?>
	<?php include("topbanner.php"); ?>
	<div id="top_bar">
	<form method="get">
	<div id="top_section">
		<?php if (isset($_GET['x']) AND $_GET['x'] == NULL) { 
			?><div id="focusborder">
		<?php } else { ?>
			<div>
		<?php } ?>
		<strong>Coordinates</strong><br>
		X <input class="coord" type=text name="x" value="<?php echo $_GET['x']; ?>"> 
		Y <input class="coord" type=text name="y" value="<?php echo $_GET['y']; ?>">
	</div>
	</div>
	<div id="top_section">
		<strong>Server</strong><br>
		<select class="server" name="sid">
	<?php foreach ($ServerList as $i=>$server) {
				if ($server['sid'] == $sid) {
					printf("<option id=\"%s\" SELECTED value=\"%d\">%s</option>", $server['country'], $server['sid'], $server['name'].".travian.".$server['country']);
					$ServerURL = $server['name'].".travian.".$server['country'];
				} else {
					printf("<option id=\"%s\" value=\"%d\">%s</option>", $server['country'], $server['sid'], $server['name'].".travian.".$server['country']);
				}
			}
	?>
		</select>
	</div>
	<div id="top_section">
		<strong>Speed</strong><br>
		<input type=text class="coord" name="speed" value="<?php printf("%d", $_GET['speed']); ?>">
	</div>
	<div id="top_section">
		<strong>Tmt. Square</strong><br>
		<select name="tsq">
		<?php
			for ($i = 0; $i<=20; $i++) {
				if ($i == $_GET['tsq']) {
					printf("<option SELECTED value=\"%d\">%d</option>", $i, $i);
				} else {
					printf("<option value=\"%d\">%d</option>", $i, $i);
				}
			}
		?>
		</select>
	</div>
	<div id="top_section">
		<strong>Alliance(s)</strong><br>
		<input id="textfield" type=text name="a" value="<?php echo $_GET['a']; ?>">
	</div>
	<div id="top_section">
		<strong>Player(s)</strong><br>
		<input id="textfield" type=text name="p" value="<?php echo $_GET['p']; ?>">
	</div>
	<div id="top_section">
		<strong>Min. v. size</strong><br>
		<input type=text name="size" class=coord value="<?php printf("%d", $_GET['size']); ?>">
	</div>

	<div id="top_section">
		<br>
		<input id="submit" type=submit>
	</div>
	</div><br style="clear: right;">
	<input type=hidden name="wl" value="<?php echo $_GET['wl']; ?>">
</form>
