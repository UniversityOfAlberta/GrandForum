<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ReportArchive'] = 'ReportArchive';
$wgExtensionMessagesFiles['ReportArchive'] = $dir . 'ReportArchive.i18n.php';
$wgSpecialPageGroups['ReportArchive'] = 'reporting-tools';

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
    
    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        $this->getOutput()->setPageTitle("Report Archive");
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

        // Check for a download.
        $action = @$_GET['getpdf'];
        if ($action !== "") {
            $tok = $action;
            
            $sto = new ReportStorage($person, null);
            if (! empty($tok)) {
                $pdf = $sto->fetch_pdf($tok, false);
                $len = $sto->metadata('len_pdf');
                $user_id = $sto->metadata('user_id');
                $project_id = $sto->get_report_project_id();
                $type = $sto->metadata('type');
                $pdf_owner = Person::newFromId($user_id);
                $pdf_project = Project::newFromHistoricId($project_id);
                $pdf_owner_name = $pdf_owner->getName();
                if ($pdf == false || $len == 0) {
                    $wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
                }
                else {
                    $ext = (strstr($type, "_ZIP") !== false) ? "zip" : "pdf";
                    $tst = $sto->metadata('timestamp');
                    // Make timestamp usable in filename.
                    $tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
                    if($wgTitle->getText() == "ReportArchive"){
                        if($ext == "zip"){
                            if($pdf_project != null && $pdf_project->getId() != 0){
                                $name = "{$pdf_project->getName()}.zip";
                            }
                            else if($pdf_owner != null && $pdf_owner->getId() != 0){
                                $name = "{$pdf_owner_name}.zip";
                            }
                            else{
                                $name = "Report.zip";
                            }
                        }
                        else{
                            $report = AbstractReport::newFromToken($tok, @$_GET['type']);
                            $year = substr($tst, 0, 4);
                            $month = substr($tst, 4, 2);
                            $day = substr($tst, 6, 2);
                            $hour = substr($tst, 9, 2);
                            $minute = substr($tst, 11, 2);
                            $date = "{$year}-{$month}-{$day}_{$hour}-{$minute}";
                            $reportName = str_replace(" ", "-", trim(str_replace(":", "", str_replace("Report", "", $report->name))));
                            if($report->project != null){
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
                                    $name = "{$lastName}".substr($firstName, 0, 1)."-{$reportName}:{$project->getName()}_{$date}.{$ext}";
                                }
                            }
                            else{
                                // Individual Reports
                                $firstName = $report->person->getFirstName();
                                $lastName = $report->person->getLastName();
                                $name = "{$lastName}".substr($firstName, 0, 1)."-{$reportName}_{$date}.{$ext}";
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
        return;
    }
    
}

?>
