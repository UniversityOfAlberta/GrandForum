<?php

$options = array('help');
require_once( 'commandLine.inc' );

function showHelp() {
	echo <<<EOT
Populate the budget percentage table.

USAGE: php insert_percentage.php <user> <project> <percent: 0..100> <year>

EOT;
}

// XXX: Adjust the destination database here:
$DATAB = "grand_forum";

if(isset($options['help'])) {
	showHelp();
	exit(1);
}

if(count($args) != 4) {
	showHelp();
	exit(1);
}

$db = mysql_connect("localhost", $wgDBuser, $wgDBpassword) or
	die("* Can't connect to MySQL.\n");

mysql_select_db($DATAB) or
	die("* Can't connect to $DATAB.\n");

echo "Database: $DATAB\n";

// Find user ID.
$r = mysql_query("SELECT user_id, user_name FROM mw_user WHERE user_name like '%{$args[0]}%';");
if (! $r) {
	die("* Error querying mw_user.\n");
}
$rows = array();
while ($row = mysql_fetch_row($r)) {
	$rows[] = $row;
}

switch (count($rows)) {
case 0:
	die("* User not found.\n");
case 1:
	// OK.
	break;
default:
	echo "Ambiguous username:\n";
	foreach ($rows as &$row) {
		echo "  {$row[1]} ({$row[0]})\n";
	}
	exit;
}

$user_id = $rows[0][0];

// Find project ID.
$r = mysql_query("SELECT nsId FROM mw_an_extranamespaces WHERE nsName = '{$args[1]}';");
if (! $r) {
	die("* Error querying mw_an_extranamespaces.\n");
}
switch (mysql_num_rows($r)) {
case 0:
	die("* Project not found.\n");
case 1:
	// OK.
	break;
default:
	die("* Duplicate project name.\n");
}
$r = mysql_fetch_row($r);
$proj_id = $r[0];

// OK to include entry.
if (! is_numeric($args[2]))
	die("* Invalid allocation: {$args[2]}\n");
if ($args[2] > 100) {
	die("* Bad allocation: {$args[2]}\n");
}
if (! is_numeric($args[3]))
	die("* Invalid year: {$args[3]}\n");

echo "Including {$args[2]}% on {$args[3]} for {$rows[0][1]}... ";
$r = mysql_query("INSERT INTO mw_allocations (user_id, project_id, allocated, budget_year) VALUES ({$user_id}, {$proj_id}, {$args[2]}, {$args[3]})");
if (! $r) {
	echo "ERROR\n" . mysql_error($r);
}
else {
	echo "OK\n";
}
