<?php

$options = array('help');
require_once( 'commandLine.inc' );

function showHelp() {
	echo( <<<EOT
Removes the "|project_milestones = ..." field and its contents from the
database for <project>.

USAGE: php milestone_replacer [--help|--commit] <project>

--help
	Show this help information
--commit
	Performs the operation instead of dry-run.

EOT
	);
}

if(isset($options['help'])) {
	showHelp();
	exit(1);
}

if(count($args) != 1) {
	showHelp();
	exit(1);
}

$project = $args[0];

$db_text = mysql_connect("localhost", $wgDBuser, $wgDBpassword) or
	die("Can't connect to MySQL");

mysql_select_db("grand_forum_ricardo", $db_text) or
	die("Can't select text database");

// Obtain the current mw_text state for the project.
$sql = "SELECT t.old_text, t.old_id
	FROM mw_page p, mw_text t, mw_an_extranamespaces ns, mw_revision r
	WHERE r.rev_text_id = t.old_id
	AND r.rev_id = p.page_latest
	AND ns.nsId = p.page_namespace
	AND CONCAT(ns.nsName, CONCAT(':', p.page_title)) = '{$project}:Main'";
$res = mysql_query($sql, $db_text);

if (mysql_num_rows($res) == 0) {
	die("No matches for SQL query <<<$sql>>>\n");
}

$row = mysql_fetch_assoc($res);

$text = $row['old_text'];
$id = $row['old_id'];
$start = strpos($text, "|project_milestones =");
if ($start === false) {
	die("Start marker not found in blob data for mw_text.old_id = {$id}\n");
}

$end = strpos($text, "|", $start + 1);
if ($end === false) {
	die("End marker not found in blob data for mw_text.old_id = {$id}\n");
}

$newtext = substr($text, 0, $start) . substr($text, $end);

if(isset($options['commit'])) {
	// Perform the action.
	print "===> Replacing milestone information for mw_text.old_id = {$id}...\n";
	$sql = "UPDATE mw_text SET old_text='$newtext' WHERE old_id='{$id}'";
	mysql_query($sql, $db_text);
	print "  affected rows: " . mysql_affected_rows() . "\nDone.\n";
}
else {
	// Dry-run.
	print "===> DRY-RUN, mw_text.old_id = {$id}\nMarked blob:\n" . substr($text, 0, $start) .
		"@@@" . substr($text, $start, $start - $end) . "@@@" .
		substr($text, $end) . "\n\nModified blob:\n$newtext\n";
}
?>
