<?php

// This script imports milestone data from an input XML.
//
// It is not supposed to be run through a web browser, only command-line.
// Normally, this script is executed during an initial import of data, on empty
// tables.
//
// The following tables are modified:
//
//	* mw_milestones_current
//	* mw_milestones_history

$options = array('help');
require_once( 'commandLine.inc' );

function showHelp() {
	echo( <<<EOT
Imports milestone information from projects given as a simple XML file.
The format is:

<PROJECT>
  <ACRONYM>PROJECT_IDENTIFIER</ACRONYM>
  <MILESTONE>
    <TITLE>Milestone title</TITLE>
    <DESCRIPTION>Description on the milestone.</DESCRIPTION>
    <ASSESSMENT>How the milestone will be assessed.</ASSESSMENT>
  </MILESTONE>
  ...
</PROJECT>
...

USAGE: php milestone_import [--help] <xml_file>

--help
	Show this help information

EOT
	);
}

// XXX: Adjust the destination database here:
$DATAB = "grand_forum_ricardo";

if(isset($options['help'])) {
	showHelp();
	exit(1);
}

if(count($args) != 1) {
	showHelp();
	exit(1);
}

$INXML = $args[0];

$db = mysql_connect("localhost", $wgDBuser, $wgDBpassword) or
	die("Can't connect to MySQL");

mysql_select_db($DATAB) or
	die("Can't connect to database");

echo "Database: $DATAB\n";

// Setup an array of projects.
$result = mysql_query("SELECT nsName, nsId from `mw_an_extranamespaces` WHERE themes <> ''", $db);
if (!$result) {
	die("Error fetching projects.");
}
$projects = array();
while ($row = mysql_fetch_row($result)) {
	// Create an array indexed by project name, having the project ID as
	// the sole content.
	$projects[$row[0]] = $row[1];
}

// Obtain the user ID for "Admin".l
$result = mysql_query("SELECT user_id FROM `mw_user` WHERE user_name = 'Admin'", $db);
if (!$result) {
	die("Error fetching Admin user_id");
}
$row = mysql_fetch_row($result);
$admin = $row[0];

function add_milestone_history() {
	global $seq;
	global $admin;
	global $projects;
	global $proj;
	global $title;
	global $desc;
	global $assess;
	global $db;
	global $commits;

	if ($proj === '') {
		die("Error: unnamed project while attempting to insert data.\n");
	}

	// Prepare textual data.
	$title = mysql_real_escape_string($title, $db);
	$desc = mysql_real_escape_string($desc, $db);
	$assess = mysql_real_escape_string($assess, $db);

	// Verify if there is a current milestone with the expected sequence number.
	// If so, it will be overwritten.  Otherwise, a new milestone blob is included
	// with its respective new current pointer.
	$sql = "SELECT h.id FROM mw_milestones_history h, mw_milestones_current c WHERE c.proj_id = {$projects[$proj]} AND h.id = c.milestone_id AND h.sequence = {$seq};";
	$res = mysql_query($sql, $db);
	if (mysql_num_rows($res) > 0) {
		// This is an update.
		$row = mysql_fetch_row($res);
		$repl_id = $row[0];

		echo "Replacing contents of milestone id=$repl_id... ";
		$sql = "UPDATE mw_milestones_history SET title='{$title}', description='{$desc}', assessment='{$assess}' WHERE id = {$repl_id};";
		$res = mysql_query($sql, $db);
		$row = mysql_affected_rows();
		if ($row > 0) {
			echo "OK\n";
		}
		else {
			echo "ERROR:\n  " . mysql_error($db) . "\n";
		}
	}
	else {
		echo "Including new milestone #$seq for $proj... ";

		// New/extra milestone.  Register it and a new current pointer.
		$q1 = "INSERT INTO `mw_milestones_history` (`sequence`, `creator`, `project`, `title`, `description`, `assessment`) VALUES ({$seq}, {$admin}, {$projects[$proj]}, '{$title}', '{$desc}', '{$assess}');";
		//echo $q1 . "\n";
		mysql_query($q1, $db);
		$row = mysql_affected_rows();
		if ($row > 0) {
			echo "OK (history)";
		}
		else {
			echo "ERROR:\n  " . mysql_error($db) . "\n";
		}

		$q2 = "INSERT INTO `mw_milestones_current` (`proj_id`, `milestone_id`) VALUES ($projects[$proj], LAST_INSERT_ID());";
		//echo $q2 . "\n";

		//echo "\n";
		mysql_query($q2, $db);
		$row = mysql_affected_rows();
		if ($row > 0) {
			echo ", OK (current)\n";
		}
		else {
			echo "ERROR:\n  " . mysql_error($db) . "\n";
		}
	}

	// For debugging:
	//echo "$proj ($seq):\n  T=" . substr($title, 0, 50) . "\n  D=" . substr($desc, 0, 50)
	//	. "\n  A=" . substr($assess, 0, 50) . "\n";
	$commits++;

	return true;
}

function get_text($from) {
	// Extract the contents between the XML tags.
	$sta = strpos($from, ">");
	$end = strrpos($from, "<", $sta + 1);
	return htmlspecialchars(substr($from, $sta + 1, $end - $sta - 1));
}

$input = fopen($INXML, "r");
if ($input === false) {
	die("Can't open '$INXML'");
}

// Parser state.
$seq = 0;
$proj = "";
$title = "";
$desc = "";
$assess = "";

// Number of entries seen in the XML.
$entries = 0;
// Number of entries committed to the database.
$commits = 0;

while (($str = fgets($input)) != false) {
	// Try to find, in order:
	// 1. <ACRONYM>
	// 2. <TITLE>
	// 3. <DESCRIPTION>
	// 4. <ASSESSMENT>

	$ind = stripos($str, "<ACRONYM>");
	if ($ind !== false) {
		if ($entries > $commits) {
			// The previous entry was not yet committed, probably
			// because it lacks an assessment tag.
			echo "  .. flushing entry on new acronym\n";
			add_milestone_history();
		}

		if ($proj != "") {
			// Reset sequence counter.
			$seq = 0;
		}

		// Get contents.
		$proj = get_text($str);
		continue;
	}

	$ind = stripos($str, "<TITLE>");
	if ($ind !== false) {
		// Clean-up other elements.
		$desc = "";
		$assess = "";
		$seq++;

		// Get contents
		$title = get_text($str);
		continue;
	}

	$ind = stripos($str, "<DESCRIPTION>");
	if ($ind !== false) {
		// Get contents
		$desc = get_text($str);
		continue;
	}

	$ind = stripos($str, "<ASSESSMENT>");
	if ($ind !== false) {
		// Get contents
		$assess = get_text($str);

		// Commit
		add_milestone_history();
		$entries++;
		continue;
	}
}

mysql_close($db);

?>
