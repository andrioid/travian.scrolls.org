<?php
	if (empty($_GET['a']) or empty($_GET['sid'])) {
		$string = "";
	} else {
		$string = sprintf("?a=%s&sid=%s", $_GET['a'], $_GET['sid']);
	}
?><div id="top_bar">
<!--		<div class="logo" style="float: left;"><img src="/img/icon_scroll.gif" border="0"></div> -->
		<h1> <a href="/">Travian Scrolls</a></h1>
		<div style="float: right;" class="toplinks">
		<a class="rdc" href="/">Relative Distance Calculator</a> -
		<a class="af" href="/af">Alliance finder</a> -
		<!-- <a class="stats" href="/stats">Statistics</a> - -->
		<a class="maps" href="/maps">Maps</a>
		</div>
	</div><br style="clear: both;">
