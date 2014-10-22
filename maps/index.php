<?php include("../config.php"); ?>
<?php include("../functions.php"); ?>
<html>
<head>
<link rel="stylesheet" href="../calc.css" type="text/css" media="screen">
<title>Travian Scrolls - Maps | A 2D Canvas game map, in HTML5</title>
</head>
<body>
<?php include("../topbanner.php"); ?>

<p>This is a HTML5 Canvas based 2d game map I created for use in my future browser game. In order to test it and get some valuable user input - I decided to release it here as a Travian map. Enjoy!</p>

<h3>Features</h3>
<ul>
<li>Map controls: North, South, East, West. Zoom In, Zoom Out.</li>
<li>Mouse controls: Zoom with scroll wheel. Double click on a position to go there.</li>
<li>Village information: A mouse-over effect showing details about the current village.</li>
<li>Dynamic size: Will function in a small element or full screen.</li>
<li>Alliances colors: Each alliance has been given a color that it is marked with.</li>
<li>World map: As long as you have a big enough screen. You can zoom out as far as you want.</li>
</ul>
<h3>Pick your server...</h3>

<ul>
<?php foreach ($Servers as $s) {
	$link = sprintf("%s?x=%d&y=%d", serverNameFix($s['dumpfile']), 0, 0);
	$link2 = sprintf("%s?zoom=%d", serverNameFix($s['dumpfile']), 1);
	?>
	<li><?php echo serverNameFix($s['dumpfile']); ?> (<a href="<?php echo $link; ?>">city view</a>, <a href="<?php echo $link2; ?>">overview</a>)</li>
<? } ?>
</ul>

<h3>Changelog</h3>
<ul>
<li><strong>15.10.2010</strong>
<p>Map now correctly handles foreign characters in player, village and alliance names. Fixed a problem in caching where the map data files were not being updated. Flag not drawn in city view for players without an alliance. City view now shows player name besides the village instead of coordinates. City view now has three different tiles (500+, 250+, 250-), resolution for tiles improved to 128px.</p>
</li>
</ul>

<h3>Create a custom link</h3>
<p>You can use this map to link to a specific position and a zoom level. You can even skip the top banner if you want the map to be semi-fullscreen.</p>

<h4>Parameters</h4>
<pre>
x/y: x/y coordinates - example: http://travian.scrolls.org/maps/s1.travian.com?x=21&y=-23
zoom: zoom level - example: http://travian.scrolls.org/maps/s1.travian.com?x=21&y=-23&zoom=18
f: fullscreen - example: http://travian.scrolls.org/maps/s1.travian.com?x=21&y=-23&zoom=18&f
</pre>

<h3>FAQ</h3>
<ul>
<li><strong>I am using an old and crappy browser, can I still use this?</strong><br>
I have updated the site so it works with Internet Explorer (at least v8). Firefox newer than 3.5 should also works and I've tested the site with Google Chrome as well.</li>
<li><strong>Can I link to a specific position?</strong><br>Yes you can. Just change "x" and "y" in the map's URL</li>
<li><strong>Can I link to a specific zoomlevel?</strong><br>Yes you can. Just change "zoomlevel" in the map's URL</li>
<li><strong>The map seems slow, why?</strong><br>At the lower zoom levels, there is a lot of data your browser has to process and there is also some load on my small test server. I suggest using Google Chrome, it appears to be the fastest.</li>
<li><strong>It doesn't work</strong><br>If you're using Google Chrome or Firefox 3.5+ and encounter any problems, please <a href="http://andrioid.net/contact">contact me</a>. If you're using Internet Explorer - It may or may not work.</li>
<li><strong>Why is your website so ugly?</strong><br>
Well, if you ask me. It's plain, not ugly. But the fact of the matter is that I am a developer - not a graphic designer. If you feel you can help with the layout of Travian scrolls, feel free to <a href="http://andrioid.net/contact">contact me</a>.</li>
<li><strong>Can you add this feature?</strong><br>
If the feature is something that will benefit any 2D game, sure. If the feature is Travian specific, not at the moment. This map was created as a part of my own efforts in creating a browser game. I will not spend much time on Travian specific features.
</ul>

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

