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
        if($user->isRegistered()){
            $person = Person::newFromWgUser();
            if($person->isLoggedIn()){
                return true;
            }
        }
        return false;
    }
    
    function execute($par){
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
                    close();
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
                                $name = str_replace(" ", "-", $caseNumber."{$lastName}".substr($firstName, 0, 1)."-".trim(str_replace(":", "", $type)))."_$date.$ext";
                            }
                            else{
                                // Individual Reports
                                $firstName = $report->person->getFirstName();
                                $lastName = $report->person->getLastName();
                                $caseNumber = $report->person->getCaseNumber();
                                $name = "{$caseNumber}-{$lastName}".substr($firstName, 0, 1)."-{$reportName}_{$date}.{$ext}";
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
        $wgOut->addHTML("<p>You can view your submitted Annual Reports below (starting from 2018):</p>
            <table class='wikitable'>");
        for($y=YEAR+1;$y>=2018;$y--){
            $wgOut->addHTML("<tr>
                <td align='center' style='padding-left:1em; padding-right:1em;'>".($y-1)."/".substr($y,2,2)."</td>");
                
            // Annual Report
            $arUrl = "";
            $ar = new DummyReport("FEC", $person, 0, $y, true);
            $check = $ar->getPDF();
            if(count($check) > 0){
                $pdf = PDF::newFromToken($check[0]['token']);
                $arUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Annual Report</a><br />";
            }
            
            // Sabbatical Application
            $sabUrl = "";
            $sab = new DummyReport("SabbaticalApplication", $person, 0, $y, true);
            $check = $sab->getPDF();
            if(count($check) > 0){
                $pdf = PDF::newFromToken($check[0]['token']);
                $sabUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Sabbatical Application</a><br />";
            }
            
            // SPOT Report
            $spotUrl = "";
            $spot = new DummyReport("SPOTs", $person, 0, $y, true);
            $check = $spot->getPDF();
            if(count($check) > 0){
                $pdf = PDF::newFromToken($check[0]['token']);
                $spotUrl = "<a href='{$pdf->getUrl()}' target='_blank'>SPOTs</a><br />";
            }
            
            // Recommendations
            $reccUrl = "";
            $daUrl = "";
            $caUrl = "";
            $ddUrl = "";
            if(date('Y-m-d') >= "$y-10-29"){
                $recc = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "CHAIR_EVALRecommendations", $y);
                $da = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "CHAIR_EVALDean Advice", $y);
                $ca = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "CHAIR_EVALChair Advice", $y);
                $dd = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "CHAIR_EVALDean Decision", $y);
                
                if(count($recc) > 0){
                    $pdf = PDF::newFromToken($recc[0]['token']);
                    $reccUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Recommendation</a><br />";
                }
                if(count($da) > 0){
                    $pdf = PDF::newFromToken($da[0]['token']);
                    $daUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Dean's Advice</a><br />";
                }
                if(count($ca) > 0){
                    $pdf = PDF::newFromToken($ca[0]['token']);
                    $caUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Chair's Advice</a><br />";
                }
                if(count($dd) > 0){
                    $pdf = PDF::newFromToken($dd[0]['token']);
                    $ddUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Dean's Decision</a><br />";
                }
            }
            
            // Sabbatical Decision
            $sab2Url = "";
            $sab2 = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_SABBATICAL_CHAIR", $y);
            if(count($sab2) > 0){
                $pdf = PDF::newFromToken($sab2[0]['token']);
                $sab2Url = "<a href='{$pdf->getUrl()}' target='_blank'>Sabbatical Decision</a><br />";
            }
            
            // Letters
            $ddUrl = "";
            $letterUrl = "";
            $letter1Url = "";
            $letter2Url = "";
            $letter3Url = "";
            $letter4Url = "";
            $varianceUrl = "";

            $dd = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "CHAIR_EVALDean Decision", $y);
            $letter = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_LETTER", $y); // Past Years
            $letter1 = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_LETTER1", $y);
            $letter2 = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_LETTER2", $y);
            $letter3 = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_LETTER3", $y);
            $letter4 = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_LETTER4", $y);
            $variance = ReportStorage::list_reports(array($person->getId()), 0, 1, 0, "RP_LETTER5", $y-1);
            if(count($dd) > 0){
                $pdf = PDF::newFromToken($dd[0]['token']);
                $ddUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Dean's Decision</a><br />";
            }
            if(count($letter) > 0){
                $pdf = PDF::newFromToken($letter[0]['token']);
                $letterUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Letter</a><br />";
            }
            if(count($letter1) > 0){
                $pdf = PDF::newFromToken($letter1[0]['token']);
                
                $blob = new ReportBlob(BLOB_TEXT, $y, 1, 0);
                $blob_address = ReportBlob::create_address("RP_LETTER1", "TABLE", "TEMPLATE", $person->getId());
                $blob->load($blob_address);
                $blob_data = $blob->getData();
                
                $letter1Url = "<a href='{$pdf->getUrl()}' target='_blank'>Letter Template {$blob_data}</a><br />";
            }
            if(count($letter2) > 0){
                $pdf = PDF::newFromToken($letter2[0]['token']);
                
                $blob = new ReportBlob(BLOB_TEXT, $y, 1, 0);
                $blob_address = ReportBlob::create_address("RP_LETTER2", "TABLE", "TEMPLATE", $person->getId());
                $blob->load($blob_address);
                $blob_data = $blob->getData();
                
                $letter2Url = "<a href='{$pdf->getUrl()}' target='_blank'>Letter Template {$blob_data}</a><br />";
            }
            if(count($letter3) > 0){
                $pdf = PDF::newFromToken($letter3[0]['token']);
                
                $blob = new ReportBlob(BLOB_TEXT, $y, 1, 0);
                $blob_address = ReportBlob::create_address("RP_LETTER3", "TABLE", "TEMPLATE", $person->getId());
                $blob->load($blob_address);
                $blob_data = $blob->getData();
                
                $letter3Url = "<a href='{$pdf->getUrl()}' target='_blank'>Letter Template {$blob_data}</a><br />";
            }
            if(count($letter4) > 0){
                $pdf = PDF::newFromToken($letter4[0]['token']);
                
                $blob = new ReportBlob(BLOB_TEXT, $y, 1, 0);
                $blob_address = ReportBlob::create_address("RP_LETTER4", "TABLE", "TEMPLATE", $person->getId());
                $blob->load($blob_address);
                $blob_data = $blob->getData();
                
                $letter4Url = "<a href='{$pdf->getUrl()}' target='_blank'>Letter Template {$blob_data}</a><br />";
            }
            if(count($variance) > 0){
                $pdf = PDF::newFromToken($variance[0]['token']);
                $varianceUrl = "<a href='{$pdf->getUrl()}' target='_blank'>Variance of Responsibilities</a><br />";
            }
            
            $wgOut->addHTML("<td align='center' style='padding-left:1em; padding-right:1em;'>{$varianceUrl}{$arUrl}{$spotUrl}{$sabUrl}</td>
                             <td align='center' style='padding-left:1em; padding-right:1em;'>{$reccUrl}{$sab2Url}</td>
                             <td align='center' style='padding-left:1em; padding-right:1em;'>{$ddUrl}{$letter1Url}{$letter2Url}{$letter3Url}{$letter4Url}</td>");
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</table>");
        self::showTemplateLegend();
        return;
    }
    
    static function showTemplateLegend(){
        global $wgOut;
        $wgOut->addHTML("<div id='legend'>
              <ul>
                <li><a href='#tabs-1'>Current (2024)</a></li>
                <li><a href='#tabs-2'>Past (pre-2024)</a></li>
              </ul>");
        $wgOut->addHTML("
            <div id='tabs-1'>
                <h3>Letter Templates Legend (updated Oct 2024)</h3>
                 <table>
                    <tr><td colspan='2'><b>End of First Probationary Period</b></tr>
                    <tr><td>Template 7&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends 2nd probationary period, FEC agrees - Article A5.03.2(a) or A5.03.4(a)</td></tr>
                    <tr><td>Template 8&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure, FEC awards 2nd probationary period - Article 5.03.4(a)</td></tr>
                    <tr><td>Template 9&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure, FEC agree - Article A5.03.4</td></tr>
                    <tr><td>Template 10&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends no further appointment - Article A5.03.1(c) (Possible Contested Case)</td></tr>
                    <tr><td>Template 11&nbsp;&nbsp;&nbsp;</td><td>Dean recommends no further appointment - Article A5.03.1(c) (Possible Contested Case)</td></tr>
                    <tr><td>Template 12&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure, FECs preliminary position is no further appointment - Article A5.03.4 (c) (Possible Reconsideration)</td></tr>
                    <tr><td>Template 13&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends no further appointment, staff member contests, FEC agrees with recommendation - Article A5.03.4(c) (Possible GAC)</td></tr>
                    <tr><td colspan='2'><b>End of Second Probationary Period</b></tr>
                    <tr><td>Template 14&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure and FEC agrees - A5.04.2(a)</td></tr>
                    <tr><td>Template 15&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends a one-year extension and FEC agrees - A5.04.2(c)</td></tr>
                    <tr><td>Template 16&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure and FEC awards an one-year extension - A5.04.2(c)</td></tr>
                    <tr><td>Template 17&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure, FEC preliminary position is no further appointment be offered - A5.04.2(b) (Possible Reconsideration Case)</td></tr>
                    <tr><td>Template 18&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends 1 year extension, FEC preliminary position is no further appointment be offered - A5.04.2(b) (Possible Reconsideration Case)</td></tr>
                    <tr><td>Template 19&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends no further appointment - A5.04.1(b) (Possible Contested Case)</td></tr>
                    <tr><td>Template 20&nbsp;&nbsp;&nbsp;</td><td>FEC reconsideration meeting upholds preliminary decision to award no further appointment - A6.21.5(h) and A6.16.7</td></tr>
                    <tr><td>Template 21&nbsp;&nbsp;&nbsp;</td><td>FEC awards tenure after member contests Department Chair's recommendation to not support tenure - A6.18.12 and A6.16.7</td></tr>
                    <tr><td colspan='2'><b>Promotion to Professor</b></tr>
                    <tr><td>Template 22&nbsp;&nbsp;&nbsp;</td><td>Department Chair supports application for promotion and preliminary position of FEC is to deny promotion</td></tr>
                    <tr><td>Template 23&nbsp;&nbsp;&nbsp;</td><td>Department Chair does not recommend promotion - A6.14.1(b) (Possible Contested Case)</td></tr>
                    <tr><td>Template 24&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends promotion and FEC agrees - A6.16.5(a)</td></tr>
                    <tr><td>Template 25&nbsp;&nbsp;&nbsp;</td><td>Department Chair does not recommend promotion and FEC awards promotion - A6.16.5(b)</td></tr>
                    <tr><td>Template 26&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends promotion and FEC denies promotion - A6.19.1(b) (Possible Reconsideration Case)</td></tr>
                    <tr><td>Template 27&nbsp;&nbsp;&nbsp;</td><td>Department Chair does not recommend promotion, staff member appears before FEC to contest, and FEC denies promotion - A6.18.12 and A6.16.7 (Possible GAC Case)</td></tr>
                    <tr><td colspan='2'><b>Incrementation</b></tr>
                    <tr><td>Template 28&nbsp;&nbsp;&nbsp;</td><td>New appointment between July 1 and October 1 (inclusive) - A6.11.1</td></tr>
                    <tr><td>Template 29&nbsp;&nbsp;&nbsp;</td><td>New appointment between October 2 and June 1 (inclusive) - A6.11.2</td></tr>
                    <tr><td colspan='2'><b>Incrementation</b></tr>
                    <tr><td>Template 30&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends ≥ 1.0 - A6.14.1(a)</td></tr>
                    <tr><td>Template 31&nbsp;&nbsp;&nbsp;</td><td>FEC supports Department Chair’s recommended incrementation of > 1.0 - A6.16.5(a)</td></tr>
                    <tr><td>Template 32&nbsp;&nbsp;&nbsp;</td><td>FEC awards higher incrementation than Department Chair’s recommendation - A6.16.5(b)</td></tr>
                    <tr><td>Template 33&nbsp;&nbsp;&nbsp;</td><td>FEC awards lower incrementation than Department Chair’s recommendation but award is still > 1.0 - A6.16.5(b)</td></tr>
                    <tr><td>Template 34&nbsp;&nbsp;&nbsp;</td><td>After reconsideration, FEC awards lower incrementation than Department Chair’s recommendation - A6.21.5(h) and A6.16.7 (Possible GAC Case)</td></tr>
                    <tr><td>Template 35&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends <1.0 - A6.14.1 (Possible Contested Case)</td></tr>
                    <tr><td>Template 36&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends <1, staff member did not contest, FEC reduces incrementation even lower - A6.18.12 and A6.16.7 (Possible Reconsideration Case)</td></tr>
                    <tr><td>Template 37&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends <1.0, staff member contests, FEC agrees with recommendation - A6.18.12 and A6.16.7 (Possible GAC Case)</td></tr>
                    <tr><td>Template 38&nbsp;&nbsp;&nbsp;</td><td>FEC agrees with Department Chair’s recommendation of <1.0, staff member does not contest - A6.16.5(a)</td></tr>
                    <tr><td>Template 39&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends > 1.0 incrementation, FEC awards <1.0 incrementation - A6.16.5(b) (Possible Reconsideration Case)</td></tr>
                    <tr><td>Template 40&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends 1.0, FECs preliminary position awarded <1.0, staff member has case reconsidered, FEC decision remains <1.0 - A6.21.5(h) and A6.16.7 (Possible GAC Case)</td></tr>
                    <tr><td>Template 41&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends 0d for the second time in three years - A6.14(a)</td></tr>
                    <tr><td>Template 42&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends a 0b, 0.50, or 0.75, staff member does not contest, FEC awards 0d incrementation - A6.16.5(b) (Possible Reconsideration Case).</td></tr>
                    
                    <tr><td colspan='2'><b>Sabbaticals</b></tr>
                    <tr><td>Template 43&nbsp;&nbsp;&nbsp;</td><td>FEC recommends approval of sabbatical, Dean accepts the FEC recommendation - A4.02.4</td></tr>
                    <tr><td>Template 44&nbsp;&nbsp;&nbsp;</td><td>FEC recommends approval of sabbatical, Dean denies due to being over quota - A4.02.4 and A4.02.8</td></tr>
                    <tr><td>Template 45&nbsp;&nbsp;&nbsp;</td><td>FEC does not recommend approval of sabbatical, Dean denies application - A4.02.4</td></tr>
                    
                    <tr><td colspan='2'><b>FSO Promotion</b></tr>
                    <tr><td>Template 50&nbsp;&nbsp;&nbsp;</td><td>Promotion to FSO III</td></tr>
                    <tr><td>Template 51&nbsp;&nbsp;&nbsp;</td><td>Promotion to FSO IV</td></tr>
                    
                    <tr><td colspan='2'><b>ATS</b></tr>
                    <tr><td>Template ATS11&nbsp;&nbsp;&nbsp;</td><td>Agree with Chair (1.0 or Higher)</td></tr>
                    <tr><td>Template ATS12&nbsp;&nbsp;&nbsp;</td><td>Contestable from ATSEC</td></tr>
                    <tr><td>Template ATS13&nbsp;&nbsp;&nbsp;</td><td>Higher than Recommendation</td></tr>
                    <tr><td>Template ATS14&nbsp;&nbsp;&nbsp;</td><td>Lower but not less than 1.0</td></tr>
                    <tr><td>Template ATS15&nbsp;&nbsp;&nbsp;</td><td>0A</td></tr>
                    <tr><td>Template ATS16&nbsp;&nbsp;&nbsp;</td><td>0B</td></tr>
                    <tr><td>Template ATS17&nbsp;&nbsp;&nbsp;</td><td>0C</td></tr>
                    <tr><td>Template ATS18&nbsp;&nbsp;&nbsp;</td><td>0D</td></tr>
                    <tr><td>Template ATS19&nbsp;&nbsp;&nbsp;</td><td>Promotion</td></tr>
                    <tr><td>Template ATS20&nbsp;&nbsp;&nbsp;</td><td>Agree for Lower than 1</td></tr>
                </table>
            </div>
            <div id='tabs-2'>
                <h3>Letter Templates Legend</h3>
                     <table>
                        <tr><td colspan='2'><b>Annual Report</b></tr>
                        <tr><td>Template 1&nbsp;&nbsp;&nbsp;</td><td>Failure to provide an annual report</td></tr>
                        <tr><td colspan='2'><b>Staff who take Childbirth/Parental Leaves</b></tr>
                        <tr><td>Template 2&nbsp;&nbsp;&nbsp;</td><td>Annual evaluations of staff who take childbirth/parental leaves</td></tr>
                        <tr><td colspan='2'><b>External Referees for Tenure or Promotion to Professor</b></tr>
                        <tr><td>Template 3&nbsp;&nbsp;&nbsp;</td><td>Requesting assistance in serving as an external referee</td>
                        <tr><td>Template 4&nbsp;&nbsp;&nbsp;</td><td>Follow-up to external referee</td>
                        <tr><td colspan='2'><b>End of First Probationary Period</b></tr>
                        <tr><td>Template 5&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends second probationary period and Dean supports recommendation</td></tr>
                        <tr><td>Template 6&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure and FEC offers a second probationary period</td></tr>
                        <tr><td>Template 7&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends that no further appointment be offered and staff member is advised of the right to appear before FEC and contest the decision</td></tr>
                        <tr><td>Template 8&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends that no further appointment be offered and staff member appears before FEC to contest Chair’s recommendation and final decision of FEC is that no further appointment be offered</td></tr>
                        <tr><td>Template 9&nbsp;&nbsp;&nbsp;</td><td>FEC makes preliminary decision that no further appointment be offered and staff member appears before FEC's reconsideration meeting and FEC upholds preliminary decision that no further appointment be offered</td></tr>
                        <tr><td colspan='2'><b>End of First or Second Probationary Period</b></tr>
                        <tr><td>Template 10&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure and FEC supports recommendation</td></tr>
                        <tr><td colspan='2'><b>End of Second Probationary Period</b></tr>
                        <tr><td>Template 11&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends that no further appointment be offered and staff member is advised of the right to appear before FEC and contest the decision</td></tr>
                        <tr><td>Template 12&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure and FEC's preliminary position is that no further appointment be offered</td></tr>
                        <tr><td>Template 13&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends a one-year extension and FEC's preliminary decision is that no further appointment be offered</td></tr>
                        <tr><td>Template 14&nbsp;&nbsp;&nbsp;</td><td>FEC makes preliminary decision that no further appointment be offered and staff member appears before FEC's reconsideration meeting and FEC upholds preliminary position that no further appointment be offered</td></tr>
                        <tr><td>Template 15&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends a one-year extension and FEC supports recommendation</td></tr>
                        <tr><td>Template 16&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends tenure and FEC extends second probationary period by one year</td></tr>
                        <tr><td>Template 17&nbsp;&nbsp;&nbsp;</td><td>Department Chair does not support tenure and staff member appears before FEC to contest the Chair’s decision and final decision is to aware tenure</td></tr>
                        <tr><td>Template 18&nbsp;&nbsp;&nbsp;</td><td>Department Chair supports application for promotion and FEC awards promotion</td></tr>
                        <tr><td>Template 19&nbsp;&nbsp;&nbsp;</td><td>Department Chair supports application for promotion and preliminary position of FEC is to deny promotion</td></tr>
                        <tr><td>Template 20&nbsp;&nbsp;&nbsp;</td><td>Department Chair does not support promotion and staff member is advised of the right to appear before FEC and contest the recommendation</td></tr>
                        <tr><td>Template 21&nbsp;&nbsp;&nbsp;</td><td>Department Chair does not support promotion and staff member appears before FEC to contest the Chair’s recommendation and the final decision of FEC is to deny promotion</td></tr>
                        <tr><td>Template 22&nbsp;&nbsp;&nbsp;</td><td>Department Chair opposes application for promotion and FEC awards promotion</td></tr>
                        <tr><td colspan='2'><b>Incrementation</b></tr>
                        <tr><td>Template 23&nbsp;&nbsp;&nbsp;</td><td>New appointments between July 1 and October 1</td></tr>
                        <tr><td>Template 24&nbsp;&nbsp;&nbsp;</td><td>New appointments between October 2 and June 1</td></tr>
                        <tr><td>Template 25&nbsp;&nbsp;&nbsp;</td><td>Non-adjudicated increment for staff who take childbirth/parental leaves</td></tr>
                        <tr><td>Template 26&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends single/multiple increment and FEC supports recommendation</td><tr>
                        <tr><td>Template 27&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends single/multiple increment and FEC decides on higher increment</td></tr>
                        <tr><td>Template 28&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends a single increment and FEC's preliminary position is less than the Chair's recommendation and staff member appeared before FEC</td></tr>
                        <tr><td>Template 29&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends less than a single increment</td></tr>
                        <tr><td>Template 30&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends less than a single increment and FEC's preliminary position is less than the Chair's recommendation</td></tr>
                        <tr><td>Template 31&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends less than a single increment and FEC at reconsideration lowers the Chair's recommended increment</td></tr>
                        <tr><td>Template 32&nbsp;&nbsp;&nbsp;</td><td>FEC concurs with Department Chair's increment recommendation of less than a single increment and staff member appears before FEC</td></tr>
                        <tr><td>Template 33&nbsp;&nbsp;&nbsp;</td><td>FEC concurs with Department Chair's increment recommendation of less than a single increment and staff member does not appear before FEC</td></tr>
                        <tr><td>Template 34&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends single/multiple increment and FEC decides on less than a single increment</td></tr>
                        <tr><td>Template 35&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends single increment and FEC decides on less than a single increment and staff member appears before FEC</td></tr>
                        <tr><td>Template 36&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends multiple increment and FEC decides on lower increment but not less than a single increment</td></tr>
                        <tr><td>Template 37&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends a 0D increment for the second time in three years</td></tr>
                        <tr><td>Template 38&nbsp;&nbsp;&nbsp;</td><td>Department Chair recommends no increment and FEC decides on a 0D increment</td></tr>
                        <tr><td colspan='2'><b>Sabbatical</b></tr>
                        <tr><td>Template 40&nbsp;&nbsp;&nbsp;</td><td>FEC recommends approval of sabbatical and Dean accepts FEC's recommendation</td></tr>
                        
                        <tr><td colspan='2'><b>FSO Promotion</b></tr>
                        <tr><td>Template 50&nbsp;&nbsp;&nbsp;</td><td>Promotion to FSO III</td></tr>
                        <tr><td>Template 51&nbsp;&nbsp;&nbsp;</td><td>Promotion to FSO IV</td></tr>
                        
                        <tr><td colspan='2'><b>ATS</b></tr>
                        <tr><td>Template ATS11&nbsp;&nbsp;&nbsp;</td><td>Agree with Chair (1.0 or Higher)</td></tr>
                        <tr><td>Template ATS12&nbsp;&nbsp;&nbsp;</td><td>Contestable from ATSEC</td></tr>
                        <tr><td>Template ATS13&nbsp;&nbsp;&nbsp;</td><td>Higher than Recommendation</td></tr>
                        <tr><td>Template ATS14&nbsp;&nbsp;&nbsp;</td><td>Lower but not less than 1.0</td></tr>
                        <tr><td>Template ATS15&nbsp;&nbsp;&nbsp;</td><td>0A</td></tr>
                        <tr><td>Template ATS16&nbsp;&nbsp;&nbsp;</td><td>0B</td></tr>
                        <tr><td>Template ATS17&nbsp;&nbsp;&nbsp;</td><td>0C</td></tr>
                        <tr><td>Template ATS18&nbsp;&nbsp;&nbsp;</td><td>0D</td></tr>
                        <tr><td>Template ATS19&nbsp;&nbsp;&nbsp;</td><td>Promotion</td></tr>
                        <tr><td>Template ATS20&nbsp;&nbsp;&nbsp;</td><td>Agree for Lower than 1</td></tr>
                    </table>
                </div>
                </div>
            <script type='text/javascript'>
              $('#legend').tabs();
            </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        
        if($person->isRole(NI) || $person->isRole("ATS")){
            $selected = @($wgTitle->getText() == "ReportArchive") ? "selected" : false;
            $tabs["ReportArchive"]['subtabs'][] = TabUtils::createSubTab("Archive", "$wgServer$wgScriptPath/index.php/Special:ReportArchive", $selected);
        }
        return true;
    }
}

?>
