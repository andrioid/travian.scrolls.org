<?php
include("../config.php");
include("../functions.php");
$ServerList = betterServerlist($Servers);
//echo "<pre>"; print_r($ServerList); echo "</pre>";

?>
<html>
<head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/jquery.tmpl.min.js"></script>
<script type="text/javascript" src="../maps/jquery.mousewheel.js"></script>
<script type="text/javascript" src="../maps/excanvas.js"></script>
<script type="text/javascript" src="../maps/scrollsMap.dev.js"></script>
<link type="text/css" href="css/travian/jquery-ui-1.8.5.custom.css" rel="Stylesheet" />
<link type="text/css" href="/css/topbar.css" rel="Stylesheet" />
<link type="text/css" href="/css/global.css" rel="Stylesheet" />
<link type="text/css" href="/maps/main.css" rel="Stylesheet" />
<link type="text/css" href="/css/main.css" rel="Stylesheet" />
<script>
	var data = {};
	data.results = [];

	function search() {
			data.qt = $('#query .queryType:checked').attr('value');
			data.x = $('#query input[name=x]').attr('value');
			data.y = $('#query input[name=y]').attr('value');
			data.speed = $('#query input[name=speed]').attr('value');
			data.server = $('#query .server').attr('value');
			data.tsq = $('#query input[name=tsq]').attr('value');
			data.size = $('#query input[name=size]').attr('value');
			data.a = $('#query input[name=a]').attr('value');
			data.p = $('#query input[name=p]').attr('value');
			data.maxd = $('#query input[name=maxd]').attr('value');
			data.idle = $('#query input[name=idle]:checked').attr('value');
			//console.log(data.tsq);

			$('#tabs').tabs({ selected: 1 });
			$('#resultStatus').html('Loading...');
			$('#resultsTable').html('');
			switch (data.qt) {
				case 'a':
					$.get('php/searchAlliances.php', data, function(data) {
						data = fixAllianceData(data);
						$('#resultStatus').html('');
						$('#resultsTable').html('<tr><th></th><th>Alliance</th><th>Villages</th><th>Pop.</th><th>Avg. vsize</th><th>Max. vsize</th><th>Min. dist</th><th>Avg. dist</th><th>Max. dist</th></tr>');
						$('#allianceTemplate').tmpl(data).appendTo('#resultsTable');
					}, 'json');
				break;
				default:
					$.get('php/searchPlayers.php', data, function(data) {
						data = fixPlayerData(data);
						$('#resultStatus').html('');
						$('#resultsTable').html('<tr><th>Player</th><th></th><th>Alliance</th><th>Village</th><th class="coord">x,y</th><th>Actions</th><th>Distance</th></tr>');
						$('#villageTemplate').tmpl(data).appendTo('#resultsTable');
						//console.log(data);
					}, 'json');					
			}
	}

	function sequentialColor(i) {
		var r, g, b;
	
		i %= 216;
		if(i < 0) {
			i = -i;
		}
		r = Math.floor(i / 36);
		i %= 36;
		g = Math.floor(i / 6);
		b = i % 6;
		return r * 0x330000 + g * 0x003300 + b * 0x000033;
	}
	function randomColor(i) {
		return sequentialColor((i<<7)%215);
	}
	function colorToString(color) {
		var cStr = color.toString(16);
	
		while(cStr.length < 6) {
			cStr = '0' + cStr;
		}
		return '#' + cStr;
	}

	function distanceToSec (speed, tsq, distance) {
		if (speed <= 0) { return 'null'; } // If it's not moving, there's really no point.
		var tt = 1;
		if (tsq == 0 || distance <= 30) {
			tt = 1;
		} else {
			tt = (1+tsq)/10;
		}
		var uspeed = speed*tt;
		var f30time = 30*3600/speed; // 30 sqrs in seconds (instead of hours)
		return Math.round(f30time+(distance-30) * 3600/uspeed);
	}

	function pad(num, size) {
		var s = num+"";
		while (s.length < size) s = "0" + s;
		return s;
	}

	function secToCounter (sec) {
		var periods = {};
		var duration = {}
		periods['hour'] = 3600;
		periods['min'] = 60;
		periods['sec'] = 1;
		$.each(periods, function(i, item) {
			if (sec >= item) {
				duration[i] = Math.floor(sec/item);
				sec -= duration[i]*item;
			} else {
				duration[i] = 0;
			}
		});
		return (pad(duration['hour'],2)+':'+pad(duration['min'], 2)+':'+pad(duration['sec'],2));
	}

	function xyToZ(x,y) {
		return (x + 401) + ((400 - y) * 801);
	}

	function fixPlayerData(d) {
		$.each(d, function(i, item) {
			if (data.speed != 0) {
				item.ttime = distanceToSec(data.speed, data.tsq, item.distance);
				item.arrival = secToCounter(item.ttime);
			}
			item.distance = item.distance.toFixed(2);
			if (item.aid > 0) {
				item.acolor = colorToString(randomColor(item.aid));
			}
			item.z = xyToZ(item.x, item.y);
			item.server = data.server;
			//console.log(item);
		});
		return d;
	}

	function fixAllianceData(d) {
		$.each(d, function(i, item) {
			if (item.aid > 0) {
				item.acolor = colorToString(randomColor(item.aid));
				item.minDistance = item.minDistance.toFixed(2);
				item.avgDistance = item.avgDistance.toFixed(2);
				item.maxDistance = item.maxDistance.toFixed(2);
				item.avgVillage = Math.round(item.avgVillage);
				item.maxVillage = Math.round(item.maxVillage);
			}
			item.server = data.server;
		});
		return d;
	}

	function hideFormElements() {
		var radio2 = $('#query #radio2:checked');

		if (radio2.length > 0) {
				$('#query .player').hide();
				$('#query .alliance').show();
		} else {
				$('#query .player').show();
				$('#query .alliance').hide();
		}
	}

	$.fn.sendtoPlanner = function(element) { // element sources or targets
		var row = $(this).closest('tr');
		var coord = $('.coord', row).text();
		coord = coord.replace(/(\(|\))/gi, '');
		coord = coord.split(',');
		switch (element) {
			case 'source':
				var te = $('#tabs-4 .sources ul.plannerList');
			break;
			default:
				var te = $('#tabs-4 .targets ul.plannerList');
		}
		var item = {};
		item.player = $('.player', row).text();
		item.coord = $('.coord', row).text();
		item.x = coord[0];
		item.y = coord[1];
		item.alliance = $('.alliance', row).text();
		$('#plannerItemTemplate').tmpl(item).appendTo(te);
		console.log(element);
	}

	$(document).ready(function() {
		$("input:submit").button();
		$("input:submit").click(function(e) {
			//e.preventDefault();
			//search();
			//console.log(data);
		});
		$("#query #radio1").change(function() {
			hideFormElements();
		});
		$("#query #radio2").change(function() {
			hideFormElements();
		});
		$("#radio").buttonset();
		$("#tabs").tabs({
   		select: function(event, ui) {
				if (ui.index == 2) { 
					$("#map").scrollsMap(data.server, Number(data.x), Number(data.y), 25); 
				}
			}
		});
		$("#resultsTable .planner.source").live("click", function(e) {
			e.preventDefault();
			$(this).sendtoPlanner('source');
		});
		$("#resultsTable .planner.target").live("click", function(e) {
			e.preventDefault();
			$(this).sendtoPlanner('target');
		});


		hideFormElements();
		<?php if (!empty($_GET['server']) || !empty($_GET['sid'])) { echo "search();"; } ?>
		//console.log(data);
	});
</script>
<script id="villageTemplate" type="text/x-jquery-tmpl">
	<tr>
	{{if tpop}}
		{{if growth > 0}}
		<td class="race${tid} player"><a href="http://${server}/spieler.php?uid=${uid}" blank="_blank">${player}</a> (<span title="Weekly growth" class="growing">+${growth}</span>)</td>
		{{else growth < 0}}
		<td class="race${tid} player"><a href="http://${server}/spieler.php?uid=${uid}" blank="_blank">${player}</a> (<span title="Weekly growth" class="shrinking">-${growth}</span>)</td>
		{{else}}
		<td class="race${tid} player"><a href="http://${server}/spieler.php?uid=${uid}" blank="_blank">${player}</a> (<span class="idle">idle</span>)</td>
		{{/if}}
	{{else}}
		<td class="race${tid} player"><a href="http://${server}/spieler.php?uid=${uid}" blank="_blank">${player}</a> (new)</td>
	{{/if}}
	<td class="color" style="background-color: ${acolor}"></td>
	<td class="alliance"><a href="http://${server}/allianz.php?aid=${aid}" target="_blank">${alliance}</a></td>
	<td class="village"><a href="http://${server}/karte.php?z=${z}" target="_blank">${village}</a> (${population})</td>
	<td class="coord">(${x},${y})</td>
	<td class="actions">
	<a class="map" href="http://${server}/karte.php?z=${z}" target="_blank">Ingame: Map</a>
	<a class="sendunits" href="http://${server}/a2b.php?z=${z}" target="_blank">Ingame: Send units</a>
<!--	<a class="planner source" href="#" title="Planner: Source">Planner: Source</a>
	<a class="planner target" href="#" title="Planner: Target">Planner: Target</a> -->
	</td>
	<td class="number distance">${distance}</td>
	{{if arrival}}
		<td class="arrival" title="Arrival time (hh:mm:ss)">${arrival}</td>
	{{/if}}
	</tr>
</script>
<script id="allianceTemplate" type="text/x-jquery-tmpl">
	<tr>
	<td class="color" style="background-color: ${acolor}"></td>
	<td class="alliance"><a href="http://${server}/allianz.php?aid=${aid}" target="_blank">${alliance}</a></td>
	<td class="number">${villageCount}</td>
	<td class="number">${populationCount}</td>
	<td class="number">${avgVillage}</td>
	<td class="number">${maxVillage}</td>
	<td class="number">${minDistance}</td>
	<td class="number">${avgDistance}</td>
	<td class="number">${maxDistance}</td>
	</tr>
</script>
<script id="plannerItemTemplate" type="text/x-jquery-tmpl">
	<li>
	<ul class="plannerItem">
		<li>(<span class="x">${x}</span>,<span class="y">${y}</span>)</li>
		<li>${player} (${alliance})</li>
	</ul>
	</li>
</script>
<title>Distance Calculator & Alliance Finder | Travian Scrolls</title>
</head>
<body>
<?php include('../topbanner.php'); ?>
<div id="query">
<form method="GET">
	<div class="fieldbox">
	<label class="title">Coordinates</label>
	<input id="x" name="x" type="text" class="number" value="<?php printf("%d", $_GET['x']); ?>">
	<input id="y" name="y" type="text" class="number" value="<?php printf("%d", $_GET['y']); ?>">
	</div>
	<div class="fieldbox">
	<label class="title">Server</label>
	<select name="server" class="server" type="text">
			<option value="null">Select a server...</option>
	<?php
		foreach ($ServerList as $s) { ?>
			<option <?php if (strtolower($s['name']) == strtolower($_GET['server']) || (isset($_GET['sid']) && $s['sid'] == $_GET['sid'])) { echo "SELECTED"; } ?> value="<?php echo $s['name']; ?>" class="<?php echo $s['cc']; ?>"><?php echo $s['name']; ?></option>
		<? } ?>
	</select>
	</div>
	<div class="fieldbox player">
	<label class="title">Speed</label>
	<input name="speed" class="number" type="text" value="<?php printf("%d", $_GET['speed']); ?>">
	</div>
	<div class="fieldbox player">
	<label class="title" title="Tournament Square">Tmt. Square</label>
	<input name="tsq" class="number" type="text" value="<?php printf("%d", $_GET['tsq']); ?>">
	</div>
	<div class="fieldbox player">
	<label class="title" title="Minimum size">Min. v. size</label>
	<input name="size" class="number" type="text" value="<?php printf("%d", $_GET['size']); ?>">
	</div>
	<div class="fieldbox player">
	<label class="title" title="Only show idle players">Idle</label>
	<input name="idle" class="number" type="checkbox" <?php if (isset($_GET['idle']) && $_GET['idle'] == 1) { echo "CHECKED"; } ?> value="1">
	</div>
	<div class="fieldbox">
	<label class="title" title="Alliance(s)">Alliance(s)</label>
	<input name="a" type="text" value="<?php printf("%s", $_GET['a']); ?>">
	</div>
	<div class="fieldbox player">
	<label class="title" title="Player(s)">Player(s)</label>
	<input name="p" type="text" value="<?php printf("%s", $_GET['p']); ?>">
	</div>
	<div class="fieldbox alliance">
	<label class="title" title="Max distance">Max dist.</label>
	<input name="maxd" class="number" type="text" value="<?php printf("%s", $_GET['maxd']); ?>">
	</div>

	<div class="fieldbox buttons">
		<input type="submit" value="Search">
	</div>
	<div class="fieldbox buttons">
		<div id="radio">
		<input class="queryType" <?php if (!isset($_GET['qt']) || $_GET['qt'] == 'p') { echo "CHECKED"; } ?> name="qt" id="radio1" type="radio" value="p"><label for="radio1">Players</label>
		<input class="queryType" <?php if (isset($_GET['qt']) && $_GET['qt'] == 'a') { echo "CHECKED"; } ?> name="qt" id="radio2" type="radio" value="a"><label for="radio2">Alliances</label>
		</div>
	</div>
</form>
</div><br style="clear:both"; ?>
	<div id="tabs">
		<ul>
		<li><a href="#tabs-1">Introduction</a></li>
		<li><a href="#tabs-2">Results</a></li>
		<li><a href="#tabs-3">Map</a></li>
		<li><a href="#tabs-4">Attack planner</a></li>
		</ul>
		<div id="tabs-1">
			<div class="pageContent">
			<h1>Welcome to Travian Scrolls</h1>
			<em>Expanding your Travian toolbox...</em>
			<h2>Featuring</h2>
			<ul>
				<li>Find your closest neighbors, to farm.</li>
				<li>View your village on a large scale with our HTML5 map.</li>
				<li>Discover what alliances could be your closest friends, or worst enemies.</li>
				<li>Plan your evil attacks, against an unsuspecting alliance.</li>
				<li>Create lists, get organized and kick ass.</li>
				<li>Use in-game links directly from our site.</li>
				<li>Distance calculator, and so much more.</li>
			</ul>
			<h2>We're working on</h2>
			<ul>
				<li><strong>Attack planner:</strong> Allows strike team leaders to organize a global scale assults within a short timeframe.</li>
				<li><strong>Filters for the map:</strong> Currently the map is optimized to view all the map. Soon, you will be able to use all our filters on the map as well.</li>
			</ul>
			</div>
		</div>
		<div id="tabs-2">
		<div id="resultStatus"></div>
		<table id="resultsTable"></table>
		</div>
		<div id="tabs-3">
			<div id="main_container"><div id="map"></div></div>
		</div>
		<div id="tabs-4">
			<div id="planner">
			<div class="sources"><ul class="plannerList"></ul></div>
			<div class="middle"></div>
			<div class="targets"><ul class="plannerList"></ul></div>
			</div>
		</div>

	</div>
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
