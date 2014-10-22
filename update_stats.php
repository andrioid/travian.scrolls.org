#!/usr/bin/php
<?php
# Cute little script to create a weekly statistics table for each server

include("config.php");
$pDBhost = mysql_connect($DB['host'], $DB['user'], $DB['pass']);
mysql_select_db("travian");

$week_number = (int)date("YW", time());

foreach($Servers as $Server) {
	$upop = $Server['name']."_upop";
	/* Making sure the table exists before we start putting stuff in there */
	mysql_query("CREATE TABLE IF NOT EXISTS `".$upop."` (
	`id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT( 8 ) NOT NULL ,
	`population` INT( 8 ) NOT NULL ,
	`week` INT( 8 ) NOT NULL ,
	INDEX ( `uid`, `week` )
	) ENGINE = MYISAM ;"
	) or die(mysql_error());
	$Query = sprintf("SELECT uid from %s WHERE week=%d LIMIT 0,1", $upop, $week_number);
	$Result = mysql_query($Query) or die(mysql_error());
	if (mysql_num_rows($Result) == 0) {
		$Query = sprintf("INSERT INTO %s ( uid, population, week )
			SELECT uid, sum( population ) AS totalpop, %d as week
			FROM `%s`
			GROUP BY uid
		",$upop,$week_number,$Server['name']);
		mysql_query($Query) or die("Insert: ".mysql_error());
	}
}

echo $week_number."\n";

?>
