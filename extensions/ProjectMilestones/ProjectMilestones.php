<?php

// $Id: ProjectMilestones.php 1699 2012-09-21 22:56:11Z dgolovan $

$projectMilestones = new ProjectMilestones();

$wgHooks['InternalParseBeforeLinks'][] = array($projectMilestones, 'showMilestones');

class ProjectMilestones {

	static function fetch_milestones($projname) {
		$pt = getTableName("an_extranamespaces");
		$ct = getTableName("milestones_current");
		$mt = getTableName("milestones_history");

		$sql = "SELECT $mt.sequence, $mt.title, $mt.description, $mt.assessment FROM $ct, $mt WHERE $mt.id = $ct.milestone_id AND $mt.project = $ct.proj_id AND $mt.project IN (SELECT nsId FROM $pt WHERE nsName = '$projname') ORDER BY $mt.sequence";
		$rows = DBFunctions::execSQL($sql);
		if (count($rows) > 0) {
			return $rows;
		}
		else {
			return false;
		}
	}
	
	function showMilestones($parser, &$text){
		global $wgTitle;
        if($wgTitle == null){
            return true;
        }
		$proj = $wgTitle->getNsText();
		$chunk = "";

		// Obtain the current milestones for the project.  This query
		// returns 4 columns:
		//	1- sequence number for a milestone group;
		//	2- milestone title;
		//	3- milestone description;
		//	4- milestone assessment.
		$res = self::fetch_milestones($proj);
		if ($res === false) {
			// Display a placeholder and terminate.
			$chunk .= "''No milestones registered.''";
		}
		else {
			// Prepare a textual/html representation.
			foreach ($res as &$row) {
				if (preg_match("(\w|\d)", $row['title']) === false) {
					$chunk .= "* ''(No title given for this milestone)''";
				}
				else {
					// Promote the single-quotes from italic to bold.
					$chunk .= "* '''" . $row['title'] . "'''";
				}
                $chunk .= "<table style='margin-left:20px;'>";
				$chunk .= "<tr><td align='right' valign='top'><b>Description:</b></td>";
				if (preg_match("(\w|\d)", $row['description']) === false) {
					$chunk .= "<td>''(none given)''</td>";
				}
				else {
					$chunk .= "<td>{$row['description']}</td>";
				}
				$chunk .= "</tr><tr>";

				$chunk .= "<tr><td align='right' valign='top'><b>Assessment:</b></td>";
				if (preg_match("(\w|\d)", $row['assessment']) === false) {
					$chunk .= "<td>''(none given)''</td>";
				}
				else {
					$chunk .= "<td>{$row['assessment']}</td>";;
				}

				$chunk .= "</table>\n";
			}
		}

		// This is for debugging the results so far:
		//$chunk .= "<pre>\nproj = $proj\nsql =\n$sql\n" . print_r($res, true) . "</pre>\n";


		// Replace the marker with the prepared text/html.
		$text = str_replace("[Project_Milestones]", $chunk, $text);
		return true;
	}
}

?>
