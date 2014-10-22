<?php
	if (!isset($_GET['q'])) { printf("Empty query."); exit(0); }
	if (!preg_match('/^(?P<server>[a-z0-9\.]+)(\/?)$/i', $_GET['q'], $match)) { printf("Invalid query."); exit(0); }
	//$server = $_GET['q'];
	$server = $match['server'];
	if (isset($_GET['x']) && is_numeric($_GET['x'])) { $x = (int)$_GET['x']; } else { $x = (int)0; }
	if (isset($_GET['y']) && is_numeric($_GET['y'])) { $y = (int)$_GET['y']; } else { $y = (int)0; }
	if (isset($_GET['zoom']) && is_numeric($_GET['zoom'])) { $zoom = (int)$_GET['zoom']; } else { $zoom = 30; }
?>
<html>
<head>
<link rel="stylesheet" href="/calc.css" type="text/css" media="screen">
<link rel="stylesheet" href="main.css" type="text/css" media="screen">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="/maps/jquery.mousewheel.js"></script>
<script type="text/javascript" src="/maps/dectocolor.js"></script>
<!--[if IE]><script src="/maps/excanvas.js"></script><![endif]-->
<script type="text/javascript" src="/maps/scrollsMap.dev.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#map").scrollsMap('<?php echo $server; ?>', <?php echo $x; ?>, <?php echo $y; ?>, <?php echo $zoom; ?>);
	});
</script>
<title>Map for <?php echo $server; ?> | Travian Scrolls - Maps</title>
</head>
<body>
<?php if (!isset($_GET['f'])) { include("../topbanner.php"); } ?>
<div id="main_container">
<div id="map"></div>
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

