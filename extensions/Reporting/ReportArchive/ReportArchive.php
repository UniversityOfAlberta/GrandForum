<?php
$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ReportArchive'] = 'ReportArchive';
$wgExtensionMessagesFiles['ReportArchive'] = $dir . 'ReportArchive.i18n.php';
$wgSpecialPageGroups['ReportArchive'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'ReportArchive::createSubTabs';

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
        ReportArchive::generateReportArchivedReportsHTML();
    }

    // Gives a listing of all the ReportArchived pdfs
    static function generateReportArchivedReportsHTML(){
        global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath;
        $person = Person::newFromWgUser();
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
            if (!empty($tok)) {
                $pdf = $sto->fetch_pdf($tok, false);
                if(isset($_GET['html'])){
                    $html = $sto->fetch_html($tok);
                    echo $html;
                    exit;
                }
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
                    if(strstr($type, "RPTP_QACVS") !== false){
                        $ext = "zip";
                    }
                    $tst = $sto->metadata('timestamp');
                    // Make timestamp usable in filename.
                    $tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
                    if($wgTitle->getText() == "ReportArchive"){
                        if(strstr($type, "RPTP_QACVS") !== false){
                            $name = str_replace("RPTP_", "", $type);
                            $name = "{$name}.zip";
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
                                $caseNumber = strip_tags($report->person->getCaseNumber($report->year));
                                $caseNumber = ($caseNumber != "") ? "{$caseNumber}-" : "";
                                $firstName = $report->person->getFirstName();
                                $lastName = $report->person->getLastName();
                                $name = str_replace(" ", "-", $caseNumber."{$lastName}".substr($lastName, 0, 1)."-".trim(str_replace(":", "", $type)))."_$date.$ext";
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
                                $caseNumber = $report->person->getCaseNumber();
                                $name = "{$caseNumber}-{$lastName}".substr($lastName, 0, 1)."-{$reportName}_{$date}.{$ext}";
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
        $wgOut->addHTML("<p>You can view your submitted Annual Reports below (starting from 2018):</p><ul>");
        $found = false;
        for($y=YEAR;$y>=2018;$y--){
            $report = new DummyReport("FEC", $person, 0, $y, true);
            $check = $report->getPDF();
            if(count($check) > 0){
                $found = true; // Found at least 1 past submission
                $pdf = PDF::newFromToken($check[0]['token']);
                $wgOut->addHTML("<li><a href='{$pdf->getUrl()}' target='_blank'><b>$y</b></a> - Submitted: {$check[0]['timestamp']}</li>");
            }
        }
        $wgOut->addHTML("</ul>");
        if(!$found){
            $wgOut->addHTML("You have submitted no Annual Reports");
        }
        return;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        
        if($person->isRole(NI)){
            $selected = @($wgTitle->getText() == "ReportArchive") ? "selected" : false;
            $tabs["ReportArchive"]['subtabs'][] = TabUtils::createSubTab("Archive", "$wgServer$wgScriptPath/index.php/Special:ReportArchive", $selected);
        }
        return true;
    }
}

?>
