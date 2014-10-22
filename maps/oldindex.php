<html>
<head>
<title>Travian Scrolls - Maps</title>
<link rel=stylesheet type="text/css" href="../calc.css">
</head>
<?php
include("../topbanner.php");
$d = dir("/home/andri/travian.scrolls.org/maps");

echo "<ul>\n";
while(false !== ($entry = $d->read())) {
	if ($entry != ".." && $entry != "." && $entry != "index.php") {
		$ut = filemtime($entry);
		printf("<li><a href=\"%s\">%s</a><br><span class=\"tinytext\">last updated: %s</span></li>\n", $entry, $entry, date("d.m.Y", $ut));
	}
}
echo "</ul>\n";

?>
