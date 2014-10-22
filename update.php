#!/usr/bin/php
<?php
include("config.php");
$tmpfile = "tmp.sql";
$structure = "structure.sql";

mysql_connect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");
chdir("/home/andri/travian.scrolls.org");

$Log = array();
function logit($text) {
	global $Log;
	$Log[] = $text;
}

foreach ($Servers as $sid => $server) {
	$dumpfile = $server['dumpfile'];
	$name = $server['name'];
	# Get the file
	$return = 0;
	$system = sprintf("wget -O %s %s 2>/dev/null 1>/dev/null", $tmpfile, $dumpfile);
	system($system, $return);
	echo "Fetching SQL dump for $name\n";
	#echo "Run: $system \n";
	if ($return == 1) {
		logit(sprintf("Database fetching for %s failed", $name));
	} else {
		if (file_exists($tmpfile)) {
			if (filesize($tmpfile) > 0) {
				$system = sprintf("mysql --host=%s --user=%s --password=%s --default-character-set=utf8 %s < %s", $DB['host'], $DB['user'], $DB['pass'], "travian", $structure);
				system($system);
				#echo "Run: $system \n";
				$system = sprintf("mysql --host=%s --user=%s --password=%s --default-character-set=utf8 %s < tmp.sql", $DB['host'], $DB['user'], $DB['pass'], "travian");
				system($system);
				#echo "Run: $system \n"
				$query = sprintf("DROP TABLE IF EXISTS %s", $name);
				mysql_query($query) or logit(mysql_error());
				$query = sprintf("RENAME TABLE %s TO %s", "x_world", $name);
				mysql_query($query) or logit(mysql_error());
				logit("Updated database for $name");
			}
		}
	}
	#echo "wget returned: $return\n";
	#echo "$name / $dumpfile \n";
}

foreach ($Log as $logentry) {
  $query = sprintf("INSERT INTO Events SET details='%s'", $logentry);
  mysql_query($query) or die(mysql_error());
  echo "Log: $logentry \n";
}

?>
