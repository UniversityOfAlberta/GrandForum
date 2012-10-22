<?php

$wgSpecialPages['SupplementalReport'] = 'SupplementalReport';
$wgExtensionMessagesFiles['SupplementalReport'] = $dir . 'SupplementalReport.i18n.php';
$wgSpecialPageGroups['SupplementalReport'] = 'reporting-tools';

function supplLocalSort($ida, $idb) {
	$a = Person::newFromId($ida);
	$a = $a->getName();
	$b = Person::newFromId($idb);
	$b = $b->getName();
	$pa = strpos($a, '.');
	if ($pa === false)
		$na = $a;
	else
		$na = substr($a, $pa) . substr($a, 0, $pa);
	$pb = strpos($b, '.');
	if ($pb === false)
		$nb = $b;
	else
		$nb = substr($b, $pb) . substr($b, 0, $pb);

	return ($na > $nb);
}


function runSupplementalReport($par) {
	global $wgServer, $wgScriptPath, $wgOut, $wgUser, $wgTitle;

	$p = Person::newFromId($wgUser->getId());
	if ($p->isPNI() || $p->isCNI()) {
		$wgOut->setPageTitle("{$p->getNameForForms()}: Supplemental Report");
		SupplementalReport::show($p, true);
	}
	else if($p->isRoleAtLeast(STAFF)){
		// Check for a GET action; show list of reports otherwise.
		// Also take care to trim out anything that is not a single number.
		$rep = ArrayUtils::get_field($_GET, 'r');
		$rep = substr($rep, 0, strspn($rep, '1234567890'));
		$bud = ArrayUtils::get_field($_GET, 'b');
		$bud = substr($bud, 0, strspn($bud, '1234567890'));
		if ($rep !== false) {
			// View read-only report.
			$p = Person::newFromId($rep);
			SupplementalReport::show($p, true);
			return true;
		}
		if ($bud !== false) {
			// Download budget.
			$p = Person::newFromId($bud);
			$pg = "Special:SupplementalReport";
			$sdxls = new SessionData($bud, $pg, SD_SUPPL_BUDGET);
			$data = $sdxls->fetch(false);
			if ($data !== false)
				SupplementalReport::download_budget("{$p->getNameForPost()}_budget_2011-12.xls", $data);
			return true;
		}

		// No action -- show list.
		SupplementalReport::list_reports();
	}
	else{
		$wgOut->setPageTitle("Supplemental Report");
		$wgOut->addHTML("You must be a PNI or CNI to access this report.");
	}
}

class SupplementalReport extends SpecialPage {
	function __construct() {
		wfLoadExtensionMessages('SupplementalReport');
		SpecialPage::SpecialPage("SupplementalReport", 'Researcher'.'+', true, 'runSupplementalReport');
	}

	/// Displays the supplemental report for user #person, optionally
	/// on read-only mode if #ro is true.
	static function show($person, $ro = false) {
		global $wgOut, $wgServer, $wgScriptPath, $wgUser;
        $me = Person::newFromId($wgUser->getId());
		if ($person === null || $person === false)
			return;

		// The folding script.
		$wgOut->addScript("
<script type='text/javascript'>
function mySelect(form){ form.select(); }
function ShowOrHide(d1, d2) {
	if (d1 != '') DoDiv(d1);
	if (d2 != '') DoDiv(d2);
}
function DoDiv(id) {
	var item = null;
	if (document.getElementById) {
		item = document.getElementById(id);
	} else if (document.all) {
		item = document.all[id];
	} else if (document.layers) {
		item = document.layers[id];
	}
	if (!item) {
	}
	else if (item.style) {
		if (item.style.display == 'none') { item.style.display = ''; }
		else { item.style.display = 'none'; }
	}
	else { item.visibility = 'show'; }
}
</script>");

		// Load session data, merge with $_POST, and store it back.
		$sdpg = "Special:SupplementalReport";
		$sd = new SessionData($person->getId(), $sdpg, SD_SUPPL_REPORT);
		$sdxls = new SessionData($person->getId(), $sdpg, SD_SUPPL_BUDGET);
		$pg = "$wgServer$wgScriptPath/index.php/Special:SupplementalReport";
		$new_post = (array)$sd->fetch();
		if ((!$ro || $me->getId() == $person->getId())) {
			$new_post = $_POST + $new_post;
			$sd->store($new_post);
			$roflag = "";
		}
		else {
			$roflag = "readonly";
		}

		// Check for a specific operation -- uploading or
		// downloading a budget spreadsheet.
		$status = "";
		$action = ArrayUtils::get_field($_POST, 'supplementalform');
		switch ($action) {
		case 'Upload Budget':
			// Process any action (upload).
			if ((!$ro || $me->getId() == $person->getId()) && isset($_FILES['budget_upload']) && file_exists($_FILES['budget_upload']['tmp_name'])) {
				// Grab the contents of the uploaded file and put
				// in the database.
				$file = $_FILES['budget_upload'];
				$fcont = file_get_contents($file['tmp_name']);
				$sdxls->store($fcont, false);
			}
			break;

		case 'Download Individual Report PDF':
			$tok = ArrayUtils::get_field($new_post, 'rep');
			if ($tok !== false) {
				$wgOut->disable();
				$repo = new ReportStorage($person);
				$repo->trigger_download($tok, "{$person->getNameForPost()}_report.pdf");
			}
			break;

		case 'Download Archived Budget':
			$xls = $sdxls->fetch(false);
			if (strlen($xls) > 0) {
				self::download_budget("{$person->getNameForPost()}_budget_2011-12.xls", $xls);
				return true;
			}
			break;

		case 'Save':
			if (! empty($_POST)) {
				$status = '<div style="width: 100%; align: center; border: solid 1px; color: #00aa00">' .
					'<p align="center">' .
					'<b>Supplemental Report saved.</b>' .
					'</p>' .
					'</div>';
			}
		}

		//$ronotice = ($ro) ? '<div style="width: 100%; align: center; border: solid 1px; color: #cc0000"><p align="center"><b>Read-only</b></p></div>' : '';
        $ronotice = "";
		$rp = new ResearcherProductivity($person);
		$did = 1;
		// The HTML chunk.
		$chunk = <<<EOT
{$ronotice}
{$status}
<form action='{$pg}' method='post' name='supplemental_report' enctype='multipart/form-data'>
EOT;
/*
		// Look for a report -- if available, offer to download it so that the user
		// has information to use.
		$rep = ReportStorage::list_latest_reports($person->getId(), SUBM, 0, RPTP_NORMAL);
		if (! (reset($rep)))
			$rep = ReportStorage::list_latest_reports($person->getId(), NOTSUBM, 0, RPTP_NORMAL);

		// If rep is not false, a report was found.
		$rep = reset($rep);
		/*
		if ($rep !== false){
			$chunk .= <<<EOT
<h3>Individual Report</h3>
<p>If you need to refer to your Individual Report PDF, you can download it by pressing the button below.</p>
<input type='hidden' name='rep' value='{$rep['token']}' />
<p align='center'><input type='submit' name='supplementalform' value='Download Individual Report PDF' /></p>
EOT;
		}

		// HQP stuff.
		$tmp = $rp->get_metric(RPST_HQPS);
		$pname = $person->getName();
		$hqparr = ArrayUtils::get_array($tmp, 'hqps');

		// Total HQPs.
		$levels = array('ugrad' => 'Undergraduate',
				'master' => 'M.Sc.',
				'phd' => 'Ph.D.',
				'postdoc' => 'Post Doctorate',
				'tech' => 'Technician',
				'other' => 'Other');
		$chunk .= <<<EOT
<h2>HQPs</h2>
<font style='color:#FF0000;'>The supplemental report is no longer in effect.  To add new HQP you can go to <a href='$wgServer$wgScriptPath/index.php/Special:AddMember' target='_blank'>Add Member</a>.  Or you can change your HQP relations at <a href='$wgServer$wgScriptPath/index.php/Special:EditRelations' target='_blank'>Edit Relations</a></font><br />
<p>
Below there are six classifications for HQPs:
<ol>
<li>Undergraduate
<li>M.Sc.
<li>Ph.D.
<li>Post Doctorate
<li>Technician
<li>Other
</ol>
</p>
<p>
Beside each classification is the number that were listed your
Individual Report that fall under that classification, and if you click
<i>details</i>, the names will be revealed.
For each category, if there are any updates to be made through March 31,
2011, please provide an updated number, and provide the basic
information, for any additional HQPs.
</p>
<table style='margin-left:20px;'>
EOT;
		foreach ($levels as $lev => $lname) {
			$arr = ArrayUtils::get_array($tmp, $lev);
			$chunk .= "<tr><th valign='top' align='right' style='width:12em'>{$lname}:<td valign='top' bgcolor='#e7e7e7'>" . count($arr);
			if (count($arr) > 0) {
				$chunk .= " <small><a href=\"javascript:ShowOrHide('did{$did}','')\">Details</a></small>\n<div id='did{$did}' style='display:none'><ol>\n";
				$did++;
				foreach ($arr as $hn)
					$chunk .= "<li>{$hn}\n";
				$chunk .= "</ol></div>";
			}
			$f = "fix_hqps_{$lev}";
			$fd = "det_hqps_{$lev}";
			$val = ArrayUtils::get_field($new_post, $f);
			$vald = ArrayUtils::get_field($new_post, $fd);
			$fld = "";
			if ($val !== false)
				$fld = "value='{$val}' ";
			$chunk .= "<tr><td align='right' valign='top'><b>Updated count:</b>" .
				"<td><input type='text' name='{$f}' size='15' maxlength='6' {$fld} {$roflag} />" .
				"<p>List HQPs ({$lname}) not included in the report, indicating their name, " .
				"whether they are Canadian or Foreign, and their gender, <i>one per line</i> " .
				"(e.g.: <tt>Jane Doe, Canadian, Female</tt>):</p>" .
				"<textarea name='{$fd}' rows='6' cols='72' {$roflag}>{$vald}</textarea>\n";
		}
		$chunk .= "</table>";


		// Publications.
		$tmp = $rp->get_metric(RPST_PUBLICATIONS);
		$tpb = ArrayUtils::get_string($tmp, 'total', "0");
		$chunk .= <<<EOT
<h2>Publications</h2>
<font style='color:#FF0000;'>The supplemental report is no longer in effect.  To add new Publications you can go to <a href='$wgServer$wgScriptPath/index.php/Special:AddPublicationPage' target='_blank'>Add Publication</a>.</font><br />
The number of publications that were included in your Individual Report
is included below.
If you click <i>details</i>, the titles of those publications will be
revealed.
If there are any updates or additions to be made through March 31, 2011,
please provide an updated number and include the details in the field
provided.
<table style='margin-left:20px;'>
<tr><th style='width:12em' align='right'>Reported:<td bgcolor='#e7e7e7'>{$tpb}
EOT;
		if ($tpb > 0) {
			$chunk .= " <small><a href=\"javascript:ShowOrHide('did{$did}','')\">Details</a></small>\n<div id='did{$did}' style='display:none'><ul>\n";
			$did++;
			$tpbarr = ArrayUtils::get_array($tmp, 'table7');
			foreach ($tpbarr as $inner) {
				foreach ($inner as $pb) {
					$words = str_word_count(ArrayUtils::get_string($pb, 'title', '(no title)'), 1);
					$guess = ceil(count($words) / 2) + 1;
					$set1 = array_slice($words, 0, $guess);
					$set2 = array_slice($words, $guess);
					$chunk .= "<li>" . join('&nbsp;', $set1) . ' ' . join(' ', $set2) .
						" <small>(" . Paper::friendly_type($pb['__type__']) . ")</small>\n";
				}
			}
			$chunk .= "</ul></div>";
		}
		$f = "fix_publications";
		$fd = "det_publications";
		$val = ArrayUtils::get_field($new_post, $f);
		$vald = ArrayUtils::get_field($new_post, $fd);
		$fld = "";
		if ($val !== false)
			$fld = "value='{$val}' ";
		$chunk .= <<<EOT
<tr><td align='right' valign='top'><b>Updated count:</b>
<td><input type='text' name='{$f}' size='15' maxlength='6' {$fld} {$roflag} />
<p>List the details of the publications not included in the report with a blank
line separating different publications, as in the examples below.
Please include <i>authors</i>, <i>title</i>, <i>journal/venue</i>,
<i>publication status</i>, and up to 3 <i>projects</i> (in order of relevance).
</p>
<pre>
Jane Doe, John Doe. A First Article. Journal of Publications (published). Project2, Project1.

John Doe, Jane Doe. A Short Paper. Proceedings of the XYZ (submitted). Project2.
</pre>
<textarea name='{$fd}' rows='6' cols='72' {$roflag}>{$vald}</textarea></table>
EOT;

		// Artifacts.
		$tmp = $rp->get_metric(RPST_ARTIFACTS);
		$arts = ArrayUtils::get_field($tmp, 'total', 0);
		$chunk .= <<<EOT
<h2>Artifacts</h2>
<font style='color:#FF0000;'>The supplemental report is no longer in effect.  To add new Artifact you can go to <a href='$wgServer$wgScriptPath/index.php/Special:AddArtifactPage' target='_blank'>Add Artifact</a>.</font><br />
<p>
The number of artifacts that were included in your Individual Report is
included below.
If you click <i>details</i>, the titles of those artifacts will be revealed.
If there are any updates or additions to be made through March 31, 2011,
please provide an updated number and include the details in the field
provided.
</p>
<table style='margin-left:20px;'>
<tr><th style='width:12em' align='right'>Reported:<td bgcolor='#e7e7e7'>{$arts}
EOT;
		if ($arts > 0) {
			$artif = ArrayUtils::get_array($tmp, 'details');
			$chunk .= " <small><a href=\"javascript:ShowOrHide('did{$did}','')\">Details</a></small>\n<div id='did{$did}' style='display:none'><ul>\n";
			foreach ($artif as $art)
				$chunk .= "<li>{$art}\n";
			$chunk .= "</ol><div>\n";
		}
		$f = "fix_artifacts";
		$fd = "det_artifacts";
		$val = ArrayUtils::get_field($new_post, $f);
		$vald = ArrayUtils::get_field($new_post, $fd);
		$fld = "";
		if ($val !== false)
			$fld = "value='{$val}' ";
		$chunk .= <<<EOT
<tr><th align='right' valign='top'>Updated count:
<td><input type='text' name='{$f}' size='15' maxlength='6' {$fld} {$roflag} />
<p>Details for the correct count:</p>
<p>List the <i>title</i> and up to 3 <i>projects</i> (in order of relevance) of
the artifacts not included in the report with a blank line separating different
artifacts, as in the examples below.
</p>
<pre>
Soundtrack for an amazing game. Project1, Project2.

A second artifact. Project2.
</pre>
<textarea name='{$fd}' rows='6' cols='72' {$roflag}>{$vald}</textarea></table>
EOT;


		// Contributions.
		$tmp = $rp->get_metric(RPST_CONTRIBUTIONS);
		$cnt = ArrayUtils::get_field($tmp, 'count', 0);
		$vol = self::dollar_format(ArrayUtils::get_field($tmp, 'total', 0));
		$chunk .= <<<EOT
<h2>Contributions</h2>
<font style='color:#FF0000;'>The supplemental report is no longer in effect.  To add new Contributions you can go to <a href='$wgServer$wgScriptPath/index.php/Special:AddContributionPage' target='_blank'>Add Contribution</a>.</font><br />
<p>
The number of contributions that were included in your Individual
Report, as well as the total value of those contributions, is included
below.
If you click <i>details</i>, the specifics of those contributions will
be revealed.
If there are any updates or additions to be made through March 31, 2011,
please provide an updated number and include the details in the field
provided.
</p>
<table style='margin-left:20px;'>
<tr><th style='width:12em' align='right'>Reported:<td bgcolor='#e7e7e7'>{$cnt}
<tr><th align='right'>Volume:<td bgcolor='#e7e7e7'>{$vol}
EOT;
		if ($cnt > 0) {
			$chunk .= " <small><a href=\"javascript:ShowOrHide('contdet{$pname}','')\">Details</a></small>\n<div id='contdet{$pname}' style='display:none'>\n";
			$details = ArrayUtils::get_array($tmp, 'details');
			foreach ($details as $detail) {
				$chunk .= "<div><ul>";
				$cash = ArrayUtils::get_array($detail, 'cash');
				sort($cash, SORT_NUMERIC);
				if ((reset($cash))) {
					$chunk .= "<li>Cash<ul>";
					foreach ($cash as $val) {
						$chunk .= '<li>' . self::dollar_format($val);
					}
					$chunk .= "</ul>\n";
				}

				$inkind = ArrayUtils::get_array($detail, 'inkind');
				sort($inkind, SORT_NUMERIC);
				if ((reset($inkind))) {
					$chunk .= "<li>In-kind<ul>";
					foreach ($inkind as $val) {
						$chunk .= '<li>' . self::dollar_format($val);
					}
					$chunk .= "</ul>";
				}
				$chunk .= "</ul></div>";
			}
			$chunk .= "</div>\n";
		}
		$f = "fix_contr_count";
		$fv = "fix_contr_volume";
		$fd = "det_contr";
		$val = ArrayUtils::get_field($new_post, $f);
		$valv = ArrayUtils::get_field($new_post, $fv);
		$vald = ArrayUtils::get_field($new_post, $fd);
		$fld = "";
		if ($val !== false)
			$fld = "value='{$val}' ";
		$fldv = "";
		if ($valv !== false)
			$fldv = "value='{$valv}' ";
		$chunk .= <<<EOT
<tr><th align='right' valign='top'>Updated count:
<td><input type='text' name='{$f}' size='15' maxlength='6' {$fld} {$roflag} />
<tr><th align='right' valign='top'>Updated volume:
<td><input type='text' name='{$fv}' size='15' maxlength='12' {$fldv} {$roflag} /> (CDN$)
<p>
List the <i>source</i>, <i>type</i>, and <i>value</i> of the contributions,
one per line (e.g.: <tt>Big Company, in-kind, $15000</tt>):
</p>
<textarea name='{$fd}' rows='6' cols='72' {$roflag}>{$vald}</textarea></table>
EOT;

		// General corrections.
		$fd = "det_general";
		$vald = ArrayUtils::get_field($new_post, $fd);
		$chunk .= <<<EOT
<h2>Other Corrections</h2>
<p>
In this field, you have the opportunity to include general corrections to
your Individual Report.
</p>
<table style='margin-left:20px;'>
<tr><th style='width:12em' align='right' valign='top'>General Corrections:
<td><textarea name='{$fd}' rows='16' cols='72' {$roflag}>{$vald}</textarea></table>
EOT;
*/
		// Budget spreadsheet.
		$urlprefix = "$wgServer$wgScriptPath/data";

		$lastup = $sdxls->last_update();
		if ($lastup !== false) {
			$xlsinfo = "<ul><li>A budget spreadsheet was last uploaded on <b>{$lastup}</b> (GMT " .
				date('P') .
				")<br /><input type='submit' name='supplementalform' value='Download Archived Budget' />";
		    $pg = "Special:SupplementalReport";
            $sd = new SessionData($person->getId(), $pg, SD_SUPPL_BUDGET);

            $data = $sd->fetch(false);
            if ($data === false) {
                echo "No data.";
                exit;
            }
            $budget = new Budget($person->getId(), SUPPLEMENTAL_STRUCTURE, $data);
            
			$xlspreview = "<h3>Budget Preview</h3>\n";
			$xlspreview .= $budget->copy()->filterCols(V_PROJ, array(""))->render();
		}
		else {
			$xlsinfo = '<ul><li><i>No budget spreadsheet has been uploaded.</i></ul>';
			$xlspreview = '';
		}

		$chunk .= "<h2>Revised Budget (2011-2012)</h2>";

		if (!$ro || $me->getId() == $person->getId()) {
			$chunk .= <<<EOT
<h3>Budget Template Spreadsheet and Filling Instructions</h3>
<p>
Below are two budget templates: one for PNIs and one for CNIs.
Please download the applicable template, and complete your revised
budget for 2011-12.
</p>
<p>
Just as with the budget request, the amounts you will be allocating to
different projects should be listed in different columns.
Please ensure that your overall total matches your funding allocation
for 2011-2012.
</p>
<p>
To complete the budget, please download the applicable file:
<ul>
<li><a href="{$urlprefix}/PNI_Budget_2011-12.xls">PNI Budget Template</a>
<li><a href="{$urlprefix}/CNI_Budget_2011-12.xls">CNI Budget Template</a>
</ul>
</p>
<p>
Please note that CNIs should indicate at least one PNI with whom they
are collaborating for each project that they include.
</p>
<h3>Uploading a Completed Budget Spreadsheet</h3>
<p>If you want to upload or update your budget spreadsheet, locate the file in your
system using the file chooser box below and then press the <em>Upload Budget</em>
button:
<p><input type='file' name='budget_upload' />
<input type='submit' name='supplementalform' value='Upload Budget' />
<h3>Existing Budget</h3>
EOT;
		}

		$chunk .= $xlspreview;
		if (! $ro) {
			$chunk .= <<<EOT
<h2>Storing</h2>
<p>At any point, you can save your report by pressing the <i>Save</i> button below.</p>
<p align='center'>
<input type='submit' name='supplementalform' value='Save' />
</p>
EOT;
		}

		$chunk .= "</form>\n";

		$wgOut->addHTML($chunk);
	}


	static function list_reports() {
		global $wgOut, $wgServer, $wgScriptPath;

		$sdpg = "Special:SupplementalReport";
		$reports = SessionData::list_users_in($sdpg, SD_SUPPL_REPORT);
		$budgets = SessionData::list_users_in($sdpg, SD_SUPPL_BUDGET);
        $pg = "$wgServer$wgScriptPath/index.php/Special:SupplementalReport";
		// Reindex.
		$data = array();
		foreach ($reports as $rep) {
			$data[$rep['user_id']] = array('report' => $rep['timestamp']);
		}
		foreach ($budgets as $bud) {
			// We're updating the array elements, so fetch it first,
			// update, and store it back.  Also note that the timestamp
			// of the budget will be the same as the report, because the
			// report is always saved (even during spreadsheet upload),
			// so there is no need to copy the budget timestamp.
			$arr = ArrayUtils::get_array($data, $bud['user_id']);
			$arr['budget'] = 1;
			$data[$bud['user_id']] = $arr;
		}

		// XXX: remove this workaround when the 'mega' update is complete on the live forum.
		// XXX: moved, since PHP apparently cannot properly handle include order.
		if (! defined('PNI')) {
			define('PNI', 'NI');
			define('CNI', 'CR');
		}

		// Gather all PNIs/CNIs, so that people that have not reported are
		// properly included in the listing.
		$pnis = Person::getAllPeople(PNI);
		$cnis = Person::getAllPeople(CNI);
		foreach ($pnis as &$pni) {
			$id = $pni->getId();
			// The trick: simply include whatever get_array returns:
			// either a filled or empty (new) array.
			$data[$id] = ArrayUtils::get_array($data, $id);
		}
		foreach ($cnis as &$cni) {
			$id = $cni->getId();
			$data[$id] = ArrayUtils::get_array($data, $id);
		}

		// Sort the array by last name, using a customized sorting function.
		uksort($data, "supplLocalSort");

		// Render a table with the format:
		// 	User | Timestamp | Report link | Budget link
		$chunk = "<table rules='all'><tr><th>Researcher<th>Last update<th>Supplemental Report<th>Supplemental Budget";
		foreach ($data as $u => $d) {
			$p = Person::newFromId($u);
			$r = ArrayUtils::get_field($d, 'report');
			$rs = ($r) ? "<a href='{$pg}?r={$u}'>View Report</a>" : "<i style='color:#a0a0a0'>(Report not available)</i>";
			$b = ArrayUtils::get_field($d, 'budget');
			$bs = ($b) ? "<a href='{$pg}?b={$u}'>Download Budget</a>" : "<i style='color:#a0a0a0'>(Budget not available)</i>";
			if($b && $p->getType() == CNI){
			    $sdxls = new SessionData($u, $sdpg, SD_SUPPL_BUDGET);
				$data2 = $sdxls->fetch(false);
			    // 1. Create a temporary file and write the spreadsheet data into the file,
                // so that PHPExcel can use it.
                $tmpn = tempnam(sys_get_temp_dir(), 'XLS');
                if ($tmpn === false) {
	                // Failed to reserve a temporary file.
	                echo "Could not reserve temp file.";
	                return false;
                }
                $tmpf = fopen($tmpn, 'w');
                if ($tmpf === false) {
	                "Could not create temp file.";
	                // TODO: log?
	                unlink($tmpn);
	                return false;
                }

                if (fwrite($tmpf, $data2) === false) {
	                // TODO: log?
	                // Error writing to temporary file.
	                echo "Could not write to temp file.";
	                fclose($tmpf);
	                unlink($tmpn);
	                return false;
                }
                fclose($tmpf);

                // 2. Instantiate the file as a PHPExcel IO object.
                try {
	                $obj = PHPExcel_IOFactory::load($tmpn);
	                $obj->setActiveSheetIndex(0);
	                $B22 = $obj->getActiveSheet()->getCell("B22")->getCalculatedValue();
	                $C22 = $obj->getActiveSheet()->getCell("C22")->getCalculatedValue();
	                $D22 = $obj->getActiveSheet()->getCell("D22")->getCalculatedValue();
	                $E22 = $obj->getActiveSheet()->getCell("E22")->getCalculatedValue();
	                $F22 = $obj->getActiveSheet()->getCell("F22")->getCalculatedValue();
	                $G22 = $obj->getActiveSheet()->getCell("G22")->getCalculatedValue();
	                $sum = $B22 + $C22 + $D22 + $E22 + $F22 + $G22;
	                $H22 = $obj->getActiveSheet()->getCell("H22")->getCalculatedValue();
	                if($sum != $H22){
	                    $bs .= " <font color='#FF0000'><small>ERROR</small></font>";
	                }
	                unset($obj);
	            }
	            catch(Exception $e){
	                
	            }
	            unlink($tmpn);
	            unset($data2);
	            unset($sdxls);
	        }
			$chunk .= "\n<tr><td>{$p->getNameForForms()}&nbsp;(<small>{$p->getType()}</small>)<td>{$r}<td>{$rs}<td>{$bs}";
		}
		$chunk .= "\n</table>";

		$wgOut->addHTML($chunk);
	}

	static function dollar_format($val) {
		return '$&nbsp;' . number_format($val, 2);
	}

	/// Issues a download for #data with the filename #name.  The callee *MUST*
	/// interrupt the script (eg: early return) so that no additional data is
	/// sent to the browser.
	static function download_budget($name, &$data) {
		global $wgOut;
		$wgOut->disable();
		ob_clean();
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Length: ' . strlen($data));
		header("Content-Disposition: attachment; filename=\"{$name}\"");
		echo $data;
		return true;
	}


	static function createTab($person) {
		global $wgServer, $wgScriptPath;

		// Show the Supplemental Report tab for a few users.
		if ($person->isCNI() || $person->isPNI() ||
				$person->getName() == 'Adrian.Sheppard' ||
				$person->getName() == 'Ricardo.Sanchez') {
			echo <<<EOT
<li class='top-nav-element'>
	<span class='top-nav-left'>&nbsp;</span>
	<a id='lnk-suppl_report' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:SupplementalReport' class='new'>Supplemental Report</a>
	<span class='top-nav-right'>&nbsp;</span>
EOT;
		}
	}
}
?>
