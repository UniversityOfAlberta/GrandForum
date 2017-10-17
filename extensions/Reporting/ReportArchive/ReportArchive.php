<?php
$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ReportArchive'] = 'ReportArchive';
$wgExtensionMessagesFiles['ReportArchive'] = $dir . 'ReportArchive.i18n.php';
$wgSpecialPageGroups['ReportArchive'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'ReportArchive::createTab';
$wgHooks['SubLevelTabs'][] = 'ReportArchive::createSubTabs';

#require_once($dir . '../Report/ReportStorage.php');

function runReportArchive($par) {
    ReportArchive::execute($par);
}

class ReportArchive extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("ReportArchive", '', true, 'runReportArchive');
    }
    
    function userCanExecute($user){
        if($user->isLoggedIn()){
            $person = Person::newFromWgUser();
            if($person->isLoggedIn()){
                return true;
            }
        }
        return false;
    }
    
    function execute(){
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
                if ($pdf == false || $len == 0) {
                    $wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
                }
                else {
                    $ext = "pdf";
                    if($type == RPTP_NI_ZIP || $type == RPTP_PROJ_ZIP || $type == RPTP_HQP_ZIP){
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
                        else if($type == RPTP_HQP_ZIP){
                            $name = "HQPReports_{$tst}.zip";
                        }
                        else{
                            $report = AbstractReport::newFromToken($tok);
                            $year = substr($tst, 0, 4);
                            $month = substr($tst, 4, 2);
                            $day = substr($tst, 6, 2);
                            $hour = substr($tst, 9, 2);
                            $minute = substr($tst, 11, 2);
                            $date = "{$year}-{$month}-{$day}_{$hour}-{$minute}";
                            $reportName = str_replace(" ", "-", trim(str_replace(":", "", str_replace("Report", "", $report->name))));
                            if($report->name == ""){
                                $name = str_replace(" ", "-", trim(str_replace(":", "", $type)))."_$date.$ext";
                            }
                            else if($report->project != null){
                                $project = $report->project;
                                if($report->person->getId() == 0){
                                    $reportName = trim(str_replace($project->getName(), "", $reportName), " \t\n\r\0\x0B-");
                                    // Project Reports
                                    $name = "{$project->getName()}-{$reportName}_{$date}.{$ext}";
                                }
                                else{
                                    // Individual Reports, but project version
                                    $firstName = $report->person->getFirstName();
                                    $lastName = $report->person->getLastName();
                                    $name = "{$lastName}".substr($lastName, 0, 1)."-{$reportName}:{$project->getName()}_{$date}.{$ext}";
                                }
                            }
                            else{
                                // Individual Reports
                                $firstName = $report->person->getFirstName();
                                $lastName = $report->person->getLastName();
                                $name = "{$lastName}".substr($lastName, 0, 1)."-{$reportName}_{$date}.{$ext}";
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
                    if(isset($_GET['download'])){                    
                        header('Content-Disposition: attachment; filename="'.$name.'"');
                    }
                    else{
                        header('Content-Disposition: inline; filename="'.$name.'"');
                    }
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    ini_set('zlib.output_compression','0');
                    echo $pdf;
                    return true;
                }
            }
        }
       
        $wgOut->addHTML("<h2>December $year</h2>");
        self::showIndividualReport($person, $year);
        if($year == 2010){
            self::showPLReport($person, $year);
        }
        else{
            self::showProjectReports($person, $year);
            if($year < 2011){
                self::showProjectComments($person, $year);
            }
        }
        if($year >= 2013){
            self::showChampionReports($person, $year);
        }

        self::generateHQPReportsHTML($person, $year, true, false);

        return;
    }
    
    static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if(!self::userCanExecute($wgUser)){
            return true;
        }
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
    
    // Shows the HQP report pdf links and buttons to re-generate them
    static function generateHQPReportsHTML($person, $year, $preview=false, $isactivehqp=false){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $viewOnly;
        if($viewOnly == true){
            $preview = true;
        }
        
        $wgOut->addHTML("<h3>HQP Reports</h3><table>");
        $noReports = $preview;
        $allHQP = $person->getHQPDuring("$year".REPORTING_PRODUCTION_MONTH, ($year+1).REPORTING_PRODUCTION_MONTH);
        $hqpProcessed = array();
        foreach($allHQP as $hqp){
            if($isactivehqp && !$hqp->isRole(HQP)){ //contradiction
                continue;
            }
            if(isset($hqpProcessed[$hqp->getId()])){ // HQP has already been processed
                continue;
            }
            
            $hqpProcessed[$hqp->getId()] = true;
            $report = new DummyReport("HQPReport", $hqp, null, $year);
            $check = $report->getPDF();
            if(count($check) == 0){
                $report = new DummyReport("NIReport", $hqp, null, $year);
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
                $wgOut->addHTML("<td><a id='tok{$hqp->getId()}' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download {$hqp->getNameForForms()}'s $year Report PDF</a></td><td>(generated <span id='tst{$hqp->getId()}'>$tst</span>)</span></td>");
                $noReports = false;
                $wgOut->addHTML("</tr>");
            }
        }
        $wgOut->addHTML("</table>");
        if($noReports){
            $wgOut->addHTML("<b>No Archived PDFs were found.</b>");
        }
    }
    
    // Displays the project summaries for the given project leader, and reporting year
    static function showProjectReports($person, $year){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut;

        $projs = $person->leadershipDuring(($year).REPORTING_START_MONTH, ($year).REPORTING_END_MONTH);
        
        foreach($projs as $proj){
            if(!$proj->clear){
                foreach($proj->getAllPreds() as $pred){
                    $projs[] = $pred;
                }
            }
        }
        $tbrows = "";
        $projNames = array();
        if(count($projs) > 0){
            $wgOut->addHTML("<h3>Project Leader Report</h3>");
            $sto = new ReportStorage($person);
            
            $plHTML = "";
            $commentHTML = "";
            $milestonesHTML = "";
            foreach ($projs as &$pj) {
                if(isset($projNames[$pj->getName()])){
                    continue;
                }
                $projNames[$pj->getName()] = true;
                $plReport = new DummyReport("ProjectReport", $person, $pj, $year);
                $commentReport = new DummyReport("ProjectReportComments", $person, $pj, $year);
                $milestonesReport = new DummyReport("ProjectReportMilestones", $person, $pj, $year);
                
                $plCheck = $plReport->getPDF();
                $commentCheck = $commentReport->getPDF();
                $milestonesCheck = $milestonesReport->getPDF();
                if (count($plCheck) > 0) {
                    $tok = $plCheck[0]['token'];
                    $sto->select_report($tok);        
                    $tst = $plCheck[0]['timestamp'];
                    $plHTML .= "<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&project={$pj->getName()}'>{$year} {$pj->getName()} Project Report PDF</a></td><td>(generated $tst)</td></tr>";
                }
                if (count($commentCheck) > 0) {
                    $tok = $commentCheck[0]['token'];
                    $sto->select_report($tok);
                    $tst = $plCheck[0]['timestamp'];
                    $commentHTML .= "<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&project={$pj->getName()}'>{$year} {$pj->getName()} Project Comments PDF</a></td><td>(generated $tst)</td></tr>";
                }
                if (count($milestonesCheck) > 0) {
                    $tok = $milestonesCheck[0]['token'];
                    $sto->select_report($tok);
                    $tst = $plCheck[0]['timestamp'];
                    $milestonesHTML .= "<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&project={$pj->getName()}'>{$year} {$pj->getName()} Project Milestones PDF</a></td><td>(generated $tst)</td></tr>";
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
    
    // Shows the individual reports for the given person and year
    static function showIndividualReport($person, $year){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $wgUser;

        $wgOut->addHTML("<h3>Individual Researcher Report</h3>");
        
        $roles = $person->getRolesDuring($year.REPORTING_CYCLE_START_MONTH, ($year+1).REPORTING_CYCLE_END_MONTH);
        $usedRoles = array();
        if(count($roles) > 0){
            $sto = new ReportStorage($person);
            foreach($roles as $role){
                if(isset($usedRoles[$role->getRole()])){
                    continue;
                }
                $check = array();
                if($role->getRole() == NI){
                    $usedRoles[NI] = true;
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
                    $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download your archived $year {$report->name} PDF</a> (generated $tst)<br />");
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
                    $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download your archived $year {$report->name} PDF</a> (generated $tst)<br />");
                }
                $check = array();
                if($role->getRole() == NI){
                    $usedRoles[NI] = true;
                    $report = new DummyReport("NIReportComments", $person, null, $year);
                    $check = $report->getPDF();
                }
                if (count($check) > 0) {
                    $tok = $check[0]['token'];
                    $sto->select_report($tok);        
                    $tst = $sto->metadata('timestamp');
                    $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download your archived $year {$report->name} PDF</a> (generated $tst)<br />");
                }
            }
        }
        else{
            $wgOut->addHTML("<b>No Archived PDFs were found.</b>");
        }
        if(isset($usedRoles[NI])){
            $alloc = $person->getAllocatedAmount($year, null, true);
            if(count($alloc) > 0){
                $wgOut->addHTML("<h3>Allocation (April 1 {$year} - March 31 ".($year + 1).")</h3>");
                $wgOut->addHTML("<table cellpadding='1' cellspacing='0' style='margin-left:15px;'><tr><th style='min-width:100px;'>Project</th><th>Amount</th></tr>");
                $sum = 0;
                foreach($alloc as $projId => $amnt){
                    $project = Project::newFromId($projId);
                    $sum += $amnt;
                    $wgOut->addHTML("<tr><td>{$project->getName()}</td><td align='right'>$".number_format($amnt, 0)."</td></tr>");
                }
                $wgOut->addHTML("<tr style='height:1px;background:#333333;'><td style='padding:0;' colspan='2'></td></tr>");
                $wgOut->addHTML("<tr><td><b>Total:</b></td><td align='right'>$".number_format($sum, 0)."</td></tr></table>");
            }
        }
    }

    // Displays the project leader comments for the given project leader, and reporting year
    static function showProjectComments($person, $year){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut;
        $pg = "$wgServer$wgScriptPath/index.php/Special:ProjectData";

        $projs = $person->leadershipDuring(($year).REPORTING_START_MONTH, ($year).REPORTING_END_MONTH);
        foreach($projs as $proj){
            if(!$proj->clear){
                foreach($proj->getAllPreds() as $pred){
                    $projs[] = $pred;
                }
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
                        $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$row['token']}&amp;project={$pj->getName()}'>{$year} {$pj->getName()} Project Comments PDF</a> (generated {$row['timestamp']})<br />");
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
    static function showPLReport($person, $year){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $wgUser;
        $type = "Project-Leader";
        $pg = "$wgServer$wgScriptPath/index.php/Special:ProjectData";

        $projs = $person->leadershipDuring(($year).REPORTING_START_MONTH, ($year).REPORTING_END_MONTH);
        foreach($projs as $proj){
            if(!$proj->clear){
                foreach($proj->getAllPreds() as $pred){
                    $projs[] = $pred;
                }
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
                            $wgOut->addHTML("<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$row['token']}&amp;project={$pj->getName()}'>{$year} {$pj->getName()} Project Summary</a></td><td>(generated {$row['created']})</td></tr>");
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
                            FROM `grand_pdf_report`
                            WHERE `token` = '{$tok}'";
                    $dt = DBFunctions::execSQL($sql);
                    $data = unserialize($dt[0]['data']);
                    $proj = @$data['proj'];
                    $tst = @$dt[0]['timestamp'];
                    $tok = $sto->select_report($tok);
                    foreach($projs as $project){
                        if($project->getId() == $proj){
                            $wgOut->addHTML("<tr><td><a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Download archived {$year} {$project->getName()} $type Report PDF</a></td><td> (generated by {$p->getNameForForms()} on $tst)</td></tr>");
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

    static function showChampionReports($person, $year){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $wgUser;
        $projs = $person->leadershipDuring(($year).REPORTING_START_MONTH, ($year).REPORTING_END_MONTH);
        if(count($projs) > 0){
            $wgOut->addHTML("<h3>Project Champion Reports</h3>");
            foreach($projs as $proj){
                if(!$proj->isSubProject() && $proj->getPhase() == 2){
                    $champs = array();
                    foreach($proj->getChampionsOn(($year+1).REPORTING_RMC_MEETING_MONTH) as $champ){
                        $champs[$champ['user']->getId()] = $champ;
                    }
                    foreach($proj->getSubProjects() as $sub){
                        foreach($sub->getChampionsOn(($year+1).REPORTING_RMC_MEETING_MONTH) as $champ){
                            $champs[$champ['user']->getId()] = $champ;
                        }
                    }
                    if(count($champs) > 0){
                        $wgOut->addHTML("<table>");
                        foreach($champs as $champ){
                            $report = new DummyReport("ChampionReportPDF", $champ['user'], $proj, $year);
                            if(isset($_GET['preview']) && 
                               isset($_GET['generatePDF']) &&
                               isset($_GET['project']) && $_GET['project'] == $proj->getName() &&
                               isset($_GET['person']) && $_GET['person'] == $champ['user']->getId() &&
                               isset($_GET['year']) && $_GET['year'] == $year){
                                $wgOut->clearHTML();
                                $report->renderForPDF();
                                $pdf = PDFGenerator::generate("{$report->person->getNameForForms()}_{$report->name}", $wgOut->getHTML(), "", $champ['user'], null, true);
                                echo $pdf;
                                exit;
                            }
                            $pdf = "";
                            $check = $report->getPDF();
                            if (count($check) > 0 && isset($check[0]['token'])) {
                                $tok = $check[0]['token'];
                                $pdf = "(<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>PDF</a>)";
                            }
                            $wgOut->addHTML("<tr><td>{$year} {$proj->getName()}: {$champ['user']->getReversedName()}</td><td>(<a target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?project={$proj->getName()}&person={$champ['user']->getId()}&generatePDF&preview&year=$year'>Preview</a>){$pdf}</td></tr>");
                        }
                        $wgOut->addHTML("</table>");
                    }
                }
            }
        }
    }
    
    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $config;
        $extras = $config->getValue('reportingExtras');
        if($extras['ReportArchive']){
            $tabs["My Archive"] = TabUtils::createTab("My Archive");
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if(!self::userCanExecute($wgUser)){
            return true;
        }
        $current_selection = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y')-1;

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
            $selected = ($wgTitle->getText() == "ReportArchive" && $current_selection == $i) ? "selected" : "";
            $tabs["My Archive"]['subtabs'][] = TabUtils::createSubTab($i, "$wgServer$wgScriptPath/index.php/Special:ReportArchive?year={$i}{$getString}", $selected);
        }
        
        return true;
    }
}

?>
