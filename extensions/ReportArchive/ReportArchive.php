<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['SkinTemplateContentActions'][] = 'ReportArchive::showTabs';

$wgSpecialPages['ReportArchive'] = 'ReportArchive';
$wgExtensionMessagesFiles['ReportArchive'] = $dir . 'ReportArchive.i18n.php';
$wgSpecialPageGroups['ReportArchive'] = 'reporting-tools';

#require_once($dir . '../Report/ReportStorage.php');

function runReportArchive($par) {
	ReportArchive::run($par);
}

class ReportArchive extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('ReportArchive');
		SpecialPage::SpecialPage("ReportArchive", INACTIVE.'+', true, 'runReportArchive');
	}
	
	function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath;
	    if(date('m') >= 3){
            $year = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y');
        }
        else{
            $year = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y') - 1;
        }
	    ReportArchive::generateReportArchivedReportsHTML($year);
    }

    // Gives a listing of all the ReportArchived pdfs
    static function generateReportArchivedReportsHTML($year){
        global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath;
		$person = Person::newFromId($wgUser->getId());
		if($person->isRoleAtLeast(STAFF) && isset($_GET['person'])){
		    $person = Person::newFromName($_GET['person']);
		    if($person->getName() == ""){
		        // Just in case the username entered is incorrect
		        $person = Person::newFromId($wgUser->getId());
		    }
		}

        $repi = new ReportIndex($person);
        // Check for a download.
        $action = @$_GET['getpdf'];
        if ($action !== "") {
            $tok = $action;
            
		    $sto = new ReportStorage($person);
	        if (! empty($tok)) {
			    $pdf = $sto->fetch_pdf($tok, false);
			    $len = $sto->metadata('len_pdf');
			    $user_id = $sto->metadata('user_id');
			    $type = $sto->metadata('type');
			    $pdf_owner = Person::newFromId($user_id);
			    $pdf_owner_name = $pdf_owner->getName();
			    if ($pdf === false || $len == 0) {
				    $wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
			    }
			    else {
			        $ext = "pdf";
			        if($type == RPTP_NI_ZIP || $type == RPTP_PROJ_ZIP){
			            $ext = "zip";
			        }
				    $tst = $sto->metadata('timestamp');
				    // Make timestamp usable in filename.
				    $tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
				    if($wgTitle->getText() == "ReportArchive"){
				        if($type == RPTP_PROJ_ZIP){
				            $name = "ProjectReports_{$tst}.zip";
				        }
				        else if($type == RPTP_NI_ZIP){
				            $name = "NIReports_{$tst}.zip";
				        }
				        else{
				            $report = AbstractReport::newFromToken($tok);
				            
				            if($report->project != null){
				                if($report->person->getId() == 0){
				                    $name = "{$report->name}.{$ext}";
				                }
				                else{
				                    $name = "{$report->person->getReversedName()} {$report->name}.{$ext}";
				                }
				            }
				            else{
				                $name = "{$report->person->getReversedName()} {$report->name}.{$ext}";
				            }
				        }
				    }
				    if ($len == 0) {
					    // No data, or no report at all.
					    $wgOut->addHTML("No reports available for download.");
					    return false;
				    }
				    // Good -- transmit it.
				    $wgOut->disable();
				    ob_clean();
				    header("Content-Type: application/{$ext}");
				    header('Content-Length: ' . $len);
				    header('Content-Disposition: attachment; filename="'.$name.'"');
				    header('Cache-Control: private, max-age=0, must-revalidate');
				    header('Pragma: public');
				    ini_set('zlib.output_compression','0');
				    echo $pdf;
				    return true;
			    }
		    }
        }
       
        $wgOut->addHTML("<h2>December $year</h2>");
        showIndividualReport($person, $year);
        if($year == 2010){
            showPLReport($person, $year);
        }
        else{
            showProjectReports($person, $year);
            if($year <= 2011){
                showProjectComments($person, $year);
            }
        }

        generateHQPReportsHTML($person, $year, true, false);

        return;
    }
    
    static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        $current_selection = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y')-1;
        
        if($wgTitle->getText() == "ReportArchive"){
            $content_actions = array();

            $getString = "";
            if(isset($_GET['person'])){
                $getString = "&person={$_GET['person']}";
                $me = Person::newFromName($_GET['person']);
                if($me->getName() == ""){
                    $me = Person::newFromId($wgUser->getId());
                }
            }
            else{
                $me = Person::newFromId($wgUser->getId());
            }
            
            $registration = $wgUser->getRegistration();
            $year = substr($registration, 0, 4);
            $month = substr($registration, 4, 2);
            
            for($i = date('Y'); $i >= $year; $i--){
                if($i == date('Y')){
                    if(date('m') >= 3){
                        $current_selection = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y');
                    }
                    else{
                        continue;
                    }
                }
                if($current_selection == $i){
                    $class = "selected";
                }
                else{
                    $class = false;
                }
                $content_actions[] = array (
                     'class' => $class,
                     'text'  => $i,
                     'href'  => "$wgServer$wgScriptPath/index.php/Special:ReportArchive?year={$i}{$getString}",
                    );
            }
        }
        return true;
    }
    
    static function createTab(){
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		
		$selected = "";
		if($wgTitle->getText() == "ReportArchive"){
		    $selected = "selected";
		}
		
		echo "<li class='top-nav-element $selected'>\n";
		echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		echo "	<a id='lnk-my_archive' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive' class='new'>My Archive</a>\n";
		echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		echo "</li>";
	}
}

// Shows the HQP report pdf links and buttons to re-generate them
function generateHQPReportsHTML($person, $year, $preview=false, $isactivehqp=false){
    global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $viewOnly;
    if($viewOnly == true){
        $preview = true;
    }
    
    $wgOut->addHTML("<h3>HQP Reports</h3><table>");
    $noReports = $preview;
    $allHQP = $person->getHQPDuring("$year".REPORTING_PRODUCTION_MONTH, ($year+1).REPORTING_PRODUCTION_MONTH);
    $hqpProcessed = array();
    foreach($allHQP as $hqp){
        if($isactivehqp && !$hqp->isHQP()){ //contradiction
            continue;
        }
        if(isset($hqpProcessed[$hqp->getId()])){ // HQP has already been processed
            continue;
        }
        
        $hqpProcessed[$hqp->getId()] = true;
        $report = new DummyReport("HQPReport", $person, null, $year);
        $check = $report->getPDF();
        if(count($check) == 0){
            $report = new DummyReport("NIReport", $person, null, $year);
            $check = $report->getPDF();
            $report->setName("HQP Report");
        }
        $sto = new ReportStorage($hqp);

        $tok = false;
        if (count($check) > 0) {
            foreach($check as $c){
                $sto->select_report($c['token']);
                $tst = $sto->metadata('timestamp');
                $tok = $sto->metadata('token');
                break;
            }
        }
        if($tok != false){
            $wgOut->addHTML("<tr>");
            $wgOut->addHTML("<td><a id='tok{$hqp->getId()}' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download {$hqp->getNameForForms()}'s $year Report PDF</a></td><td>(submitted <span id='tst{$hqp->getId()}'>$tst</span>)</span></td>");
            $noReports = false;
            $wgOut->addHTML("</tr>");
        }
    }
    $wgOut->addHTML("</table>");
    if($noReports){
        $wgOut->addHTML("<b>No Archived PDFs were found.</b>");
    }
}

// Shows the individual reports for the given person and year
function showIndividualReport($person, $year){
    global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $wgUser;

    $wgOut->addHTML("<h3>Individual Researcher Report</h3>");
    
    $roles = $person->getRolesDuring($year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH);
    $usedRoles = array();
    if(count($roles) > 0){
        $sto = new ReportStorage($person);
        foreach($roles as $role){
            if(isset($usedRoles[$role->getRole()])){
                continue;
            }
            $check = array();
            if($role->getRole() == PNI || $role->getRole() == CNI){
                $usedRoles[PNI] = true;
                $usedRoles[CNI] = true;
                $report = new DummyReport("NIReport", $person, null, $year);
                $check = $report->getPDF();
            }
            if($role->getRole() == HQP){
                $usedRoles[HQP] = true;
                if($year == 2010){
                    $wgOut->addHTML("<b>The HQP reports were not archived in PDF during the $year reporting period.</b><br />");
                }
                $report = new DummyReport("HQPReport", $person, null, $year);
                $check = $report->getPDF();
                if(count($check) == 0){
                    $report = new DummyReport("NIReport", $person, null, $year);
                    $check = $report->getPDF();
                    $report->setName("HQP Report");
                }
            }
            if (count($check) > 0) {
        		$tok = $check[0]['token'];
        		$sto->select_report($tok);    	
        		$tst = $sto->metadata('timestamp');
        		$wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download your archived $year {$report->name} PDF</a> (submitted $tst)<br />");
        	}
        	$check = array();
        	if($role->getRole() == HQP){
                $usedRoles[HQP] = true;
                $report = new DummyReport("HQPReportComments", $person, null, $year);
                $check = $report->getPDF();
            }
            if (count($check) > 0) {
        		$tok = $check[0]['token'];
        		$sto->select_report($tok);    	
        		$tst = $sto->metadata('timestamp');
        		$wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download your archived $year {$report->name} PDF</a> (submitted $tst)<br />");
        	}
        	$check = array();
        	if($role->getRole() == PNI || $role->getRole() == CNI){
                $usedRoles[PNI] = true;
                $usedRoles[CNI] = true;
                $report = new DummyReport("NIReportComments", $person, null, $year);
                $check = $report->getPDF();
            }
            if (count($check) > 0) {
        		$tok = $check[0]['token'];
        		$sto->select_report($tok);    	
        		$tst = $sto->metadata('timestamp');
        		$wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download your archived $year {$report->name} PDF</a> (submitted $tst)<br />");
        	}
        }
    }
    else{
        $wgOut->addHTML("<b>No Archived PDFs were found.</b>");
    }
}


// Displays the project summaries for the given project leader, and reporting year
function showProjectReports($person, $year){
    global $wgTitle, $wgServer, $wgScriptPath, $wgOut;

    $projs = $person->leadershipDuring(($year).REPORTING_PRODUCTION_MONTH, ($year+1).REPORTING_PRODUCTION_MONTH);

    foreach($projs as $proj){
        foreach($proj->getAllPreds() as $pred){
            $projs[] = $pred;
        }
    }
    $tbrows = "";

    if(count($projs) > 0){
        $wgOut->addHTML("<h3>Project Leader Report</h3>");
        $sto = new ReportStorage($person);
        
        $plHTML = "";
        $commentHTML = "";
        $milestonesHTML = "";
        foreach ($projs as &$pj) {
            $plReport = new DummyReport("ProjectReport", $person, $pj, $year);
            $commentReport = new DummyReport("ProjectReportComments", $person, $pj, $year);
            $milestonesReport = new DummyReport("ProjectReportMilestones", $person, $pj, $year);
            
            $plCheck = $plReport->getPDF();
            $commentCheck = $commentReport->getPDF();
            $milestonesCheck = $commentReport->getPDF();
            if (count($plCheck) > 0) {
        		$tok = $plCheck[0]['token'];
        		$sto->select_report($tok);    	
        		$tst = $plCheck[0]['timestamp'];
        		$plHTML .= "<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&project={$pj->getName()}'>{$year} {$pj->getName()} Project Report PDF</a></td><td>(submitted $tst)</td></tr>";
        	}
        	if (count($commentCheck) > 0) {
        		$tok = $commentCheck[0]['token'];
        		$sto->select_report($tok);
        		$tst = $plCheck[0]['timestamp'];
        		$commentHTML .= "<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&project={$pj->getName()}'>{$year} {$pj->getName()} Project Comments PDF</a></td><td>(submitted $tst)</td></tr>";
        	}
        	if (count($milestonesCheck) > 0) {
        		$tok = $milestonesCheck[0]['token'];
        		$sto->select_report($tok);
        		$tst = $plCheck[0]['timestamp'];
        		$milestonesHTML .= "<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&project={$pj->getName()}'>{$year} {$pj->getName()} Project Milestones PDF</a></td><td>(submitted $tst)</td></tr>";
        	}
        }
        if($plHTML != "" || $commentHTML != "" || $milestonesHTML != ""){
            $wgOut->addHTML("<table>$plHTML</table><br /><table>$commentHTML</table><br /><table>$milestonesHTML</table>");
        }
        else{
            $wgOut->addHTML("<b>No Archived PDFs were found.</b><br />");
        }
    }
}

// Displays the project leader comments for the given project leader, and reporting year
function showProjectComments($person, $year){
    global $wgTitle, $wgServer, $wgScriptPath, $wgOut;
    $pg = "$wgServer$wgScriptPath/index.php/Special:ProjectData";

    $projs = $person->leadershipDuring(($year).REPORTING_PRODUCTION_MONTH, ($year+1).REPORTING_PRODUCTION_MONTH);
    foreach($projs as $proj){
        foreach($proj->getAllPreds() as $pred){
            $projs[] = $pred;
        }
    }
    $tbrows = "";
    if(count($projs) > 0){
        $wgOut->addHTML("<br />");
        $sto = new ReportStorage($person);
        $pjs_done = array();
        $found = false;
        foreach ($projs as &$pj) {
            // Looping through everybody is pretty slow, but for the moment, is an easy way to find all the leader reports
            $ls = $sto->list_project_reports($pj->getId(), 10000, 0, RPTP_LEADER_COMMENTS);
            foreach ($ls as &$row) {
                if($row['year'] == $year){
	                $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$row['token']}&amp;project={$pj->getName()}'>{$year} {$pj->getName()} Project Comments PDF</a> (submitted {$row['timestamp']})<br />");
	                $found = true;
	                break;
	            }
            }
            //if(count($ls) > 0){
            //    break;
            //}
        }
    }    
}

// Displays the project leader reports for the given project leader and reporting year
function showPLReport($person, $year){
    global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $wgUser;
    $type = "Project-Leader";
    $pg = "$wgServer$wgScriptPath/index.php/Special:ProjectData";

    $projs = $person->leadershipDuring(($year).REPORTING_PRODUCTION_MONTH, ($year+1).REPORTING_PRODUCTION_MONTH);
    foreach($projs as $proj){
        foreach($proj->getAllPreds() as $pred){
            $projs[] = $pred;
        }
    }
    $tbrows = "";
    if(count($projs) > 0){
        $wgOut->addHTML("<h3>Project Leader Report</h3>");
        $repi = new ReportIndex($person);
        $pjs_done = array();
        $wgOut->addHTML("<table>");
        foreach ($projs as &$pj) {
            foreach(Person::getAllPeople() as $p){
                // Looping through everybody is pretty slow, but for the moment, is an easy way to find all the leader reports
                $repi = new ReportIndex($p);
	            $ls = $repi->list_reports($pj);
	            foreach ($ls as &$row) {
	                if($row['created'] >= ($year).REPORTING_PRODUCTION_MONTH && $row['created'] <= ($year+1).REPORTING_PRODUCTION_MONTH){
		                $wgOut->addHTML("<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$row['token']}&amp;project={$pj->getName()}'>{$year} {$pj->getName()} Project Summary</a></td><td>(submitted {$row['created']})</td></tr>");
		            }
	            }
	            if(count($ls) > 0){
	                break;
	            }
	        }
        }
        $wgOut->addHTML("</table><br /><table>");
        $found = false;
        foreach(Person::getAllPeople() as $p){
            $sto = new ReportStorage($p);
            // Leader reports, submitted.
            $check = $sto->list_reports($p->getId(), SUBM, 1000, 0, RPTP_LEADER);
            $tok = false;
            $tst = '';
            foreach($check as $r){
                if($r['timestamp'] >= ($year).REPORTING_PRODUCTION_MONTH && $r['timestamp'] <= ($year+1).REPORTING_PRODUCTION_MONTH){
                    $tst = $r['timestamp'];
	                $tok = $sto->select_report($r['token']);
	                break;
	            }
            }
            // Try unsubmitted reports.
            $check = $sto->list_reports($p->getId(), NOTSUBM, 1000, 0, RPTP_LEADER);
            foreach($check as $r){
                if($r['timestamp'] >= ($year).REPORTING_PRODUCTION_MONTH && $r['timestamp'] <= ($year+1).REPORTING_PRODUCTION_MONTH){
                    if($tok != false && $tst > $r['timestamp']){
                        break;
                    }
                    else{
                        $tok = $sto->select_report($r['token']);
                        break;
                    }
                }
            }
            if($tok != false){
                $sql = "SELECT `data`,`timestamp`
	                    FROM `mw_pdf_report`
	                    WHERE `token` = '{$tok}'";
                $dt = DBFunctions::execSQL($sql);
                $data = unserialize($dt[0]['data']);
                $proj = @$data['proj'];
                $tst = @$dt[0]['timestamp'];
	            $tok = $sto->select_report($tok);
		        foreach($projs as $project){
		            if($project->getId() == $proj){
		                $wgOut->addHTML("<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download archived {$year} {$project->getName()} $type Report PDF</a></td><td> (submitted by {$p->getNameForForms()} on $tst)</td></tr>");
		                $found = true;
		                break;
		            }
		        }
            }
        }
        $wgOut->addHTML("</table>");
        if(!$found){
            $wgOut->addHTML("<b>No Archived PDFs were found.</b>");
        }
    }
}

?>
