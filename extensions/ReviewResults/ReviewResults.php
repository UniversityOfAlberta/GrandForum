<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReviewResults'] = 'ReviewResults';
$wgExtensionMessagesFiles['ReviewResults'] = $dir . 'ReviewResults.i18n.php';
$wgSpecialPageGroups['ReviewResults'] = 'grand-tools';

require_once($dir . '../../Classes/PHPExcel/IOFactory.php');

function runReviewResults($par) {
    ReviewResults::run($par);
}

class ReviewResults extends SpecialPage {

    function __construct() {
        wfLoadExtensionMessages('ReviewResults');
        SpecialPage::SpecialPage("ReviewResults", STAFF.'+', true, 'runReviewResults');
    }
    
    function run(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        $type = "PNI";
        if(!empty($_GET['type']) && $_GET['type'] == 'CNI'){
            $type = "CNI";
        }
        else if(!empty($_GET['type']) && $_GET['type'] == 'Project'){
            $type = "Project";
        }
        else if(!empty($_GET['type']) && $_GET['type'] == 'LOI'){
            $type = "LOI";
        }
        
        if($type == 'LOI'){
            ReviewResults::loi_routine();
        }
        else{
            ReviewResults::ni_routine();
        }

        $html =<<<EOF
        <script type='text/javascript'>
        $(document).ready(function(){        
            $('#ackTabs').tabs();
        });
        </script>
        <script language="javascript" type="text/javascript" src="$wgServer$wgScriptPath/scripts/jquery.validate.min.js"></script>
            <script type="text/javascript">        
            $(function() {
                $("#resultsForm").validate();
              });
            </script>
            <style type="text/css">
            td.label {
                width: 200px;
                background-color: #F3EBF5;
                vertical-align: middle;
            }
            td input[type=text]{
                width: 240px;
            }
            td textarea {
                height: 150px;
            }
            label.error { 
                float: none; 
                color: red;  
                vertical-align: top; 
                display: block;
                background: none;
                padding: 0 0 0 5px;
                margin: 2px;
                width: 240px;
            }
            input.error {
                background: none;
                background-color: #FFF !important;
                padding: 3px 3px;
                margin: 2px;
            }
            span.requ {
                font-weight:bold;
                color: red;
            }
            </style>
            <div id='ackTabs'>
            <ul>
            <li><a href='#pni'>PNI</a></li>
            <li><a href='#cni'>CNI</a></li>
            <li><a href='#project'>Project</a></li>
            <li><a href='#loi'>LOI</a></li>
            </ul>
EOF;
        
        $html .= "<div id='pni' style='width: 100%; overflow: auto;'>";
        $html .= ReviewResults::reviewResults('PNI');
        $html .= "</div>";

        $html .= "<div id='cni' style='width: 100%; overflow: auto;'>";
        $html .= ReviewResults::reviewResults('CNI');
        $html .= "</div>";
        
        $html .= "<div id='project' style='width: 100%; overflow: auto;'>";
        $html .= ReviewResults::reviewResults('Project');
        $html .= "</div>";

        $html .= "<div id='loi' style='width: 100%; overflow: auto;'>";
        $html .= ReviewResults::reviewLOIResults();
        $html .= "</div>";

        $html .=<<<EOF
        </div>
EOF;
        
        $wgOut->addHTML($html);
    }

    static function ni_routine(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        $type = @$_GET['type'];
        if(isset($_POST['submit'])){
            $submit_val = $_POST['submit'];
            if($submit_val == "Send out Emails"){
                ReviewResults::emailAllPDFs($type);
            }
            else{
                ReviewResults::handleSubmit();
            }
        }
        else if(isset($_GET['generatePDF'])){
            ReviewResults::generateAllFeedback($type);
            exit;
        }
        else if(isset($_GET['getPDF'])){
            $filename = $_GET['getPDF'] .".March2014.pdf";
            $file = "data/review-feedback/{$type}/{$filename}";
            if(file_exists($file)){
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($file));
                header('Accept-Ranges: bytes');

                @readfile($file);
                exit;
            }
        }
        else if(isset($_GET['emailPDF'])){
            $ni_id = $_GET['emailPDF'];
            ReviewResults::emailPDF($ni_id, $type);
        }

       // ReviewResults::reviewResults($type);
    }

    static function loi_routine(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;

        if(isset($_POST['submit'])){
            $submit_val = $_POST['submit'];
            //if($submit_val == "Send out Emails"){
                ReviewResults::emailAllLOIPDFs('LOI');
            //}
            
        }
        else if(isset($_GET['generatePDF'])){
            ReviewResults::generateAllFeedback('LOI');
            exit;
        }

       // ReviewResults::reviewLOIResults();
    }

    static function emailAllPDFs($type){
        global $wgUser, $wgMessage;
        $curr_year = REPORTING_YEAR;

        $query =<<<EOF
        SELECT * FROM grand_review_results
        WHERE year={$curr_year} AND type = '{$type}' AND email_sent = 0
EOF;
    
        $sent_success = array();
        $sent_fail = array();
        $file_fail = array();

        $data = DBFunctions::execSQL($query);
        foreach($data as $row){
            if($row['send_email'] == 0){
                continue;
            }
            $ni_id = $row['user_id'];
            $ni = Person::newFromId($ni_id);
            $ni_name = $ni->getNameForForms();

            $error = ReviewResults::emailPDF($ni_id, $type);
            if($error == 0){
                $sent_success[] = $ni_name;
            }
            else if($error == 1){
                $sent_fail[] = $ni_name;
            }
            else if($error == 2){
                $file_fail[] = $ni_name;
            }
        }
        if(!empty($sent_success)){
            $message = "Email was sent successfully to the following:<br />";
            $message .= implode("<br />", $sent_success);
            $wgMessage->addSuccess($message);
        }

        if(!empty($sent_fail)){
            $message = "There was a problem sending emails to the following:<br />";
            $message .= implode("<br />", $sent_fail);
            $wgMessage->addError($message);
        }

        if(!empty($file_fail)){
            $message = "There was a problem retrieving PDFs for the following:<br />";
            $message .= implode("<br />", $file_fail);
            $wgMessage->addError($message);
        }
    }

    static function emailAllLOIPDFs(){
        global $wgUser, $wgMessage;
        $curr_year = REPORTING_YEAR;

        $lois = LOI::getAssignedLOIs($curr_year);

        $query = "SELECT * FROM grand_review_results WHERE year={$curr_year} AND type = 'LOI' AND user_id= %d";
    
        $sent_success = array();
        $sent_fail = array();
        $file_fail = array();
        
        foreach($lois as $loi){
            $loi_id = $loi->getId();
            $sql = sprintf($query, $loi_id);
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0 && $data[0]['email_sent'] == 1){
                continue;
            }

            //if($loi_id == 2 || $loi_id == 30){
                
            $loi_name = $loi->getName();

            $error = ReviewResults::emailLOIPDF($loi_id);
            if($error == 0){
                $sent_success[] = $loi_name;
            }
            else if($error == 1){
                $sent_fail[] = $loi_name;
            }
            else if($error == 2){
                $file_fail[] = $loi_name;
            }
            //}
        }

        if(!empty($sent_success)){
            $message = "Email was sent successfully to the following:<br />";
            $message .= implode("<br />", $sent_success);
            $wgMessage->addSuccess($message);
        }

        if(!empty($sent_fail)){
            $message = "There was a problem sending emails to the following:<br />";
            $message .= implode("<br />", $sent_fail);
            $wgMessage->addError($message);
        }

        if(!empty($file_fail)){
            $message = "There was a problem retrieving PDFs for the following:<br />";
            $message .= implode("<br />", $file_fail);
            $wgMessage->addError($message);
        }
    }

    static function emailLOIPDF($loi_id){
        global $wgUser, $wgMessage;
        $loi = LOI::newFromId($loi_id);
        $loi_name = $loi->getName();
        //$loi_email = $ni->getEmail();
        ///$ni_name_good = $ni->getNameForForms();

        $lead = $loi->getLeadEmail();
        if(!empty($lead['email'])){
            $lead_email = $lead['email'];
        }
        else{
            $lead_email = "adrian_sheppard@gnwc.ca";
        }

        $colead = $loi->getCoLeadEmail();    
        if(!empty($colead['email'])){
            $colead_email = $colead['email'];
        }
        else{
            $colead_email = "";
        }

        $to = $lead_email;
        if(!empty($colead_email)){
            $to .= ", ".$colead_email;
        }

        //$to = "dgolovan@gmail.com, adrian_sheppard@gnwc.ca";  
        $subject = "GRAND NCE - {$loi_name} Feedback and Next Steps";
        
        $email_body =<<<EOF
This message is going out to the LOI Submission project leaders and co-leaders. Attached you will find the specific feedback from the reviews of your project or subproject LOI Submission, as well as some instructions and guidance regarding the next steps in the process.

Included in the attached is an initial draft of GRAND's portfolio of research projects for Phase 2. The purpose of this initial draft portfolio is to provide guidance on how each of the LOI Submissions (full projects and subprojects) might fit into the anticipated 20-25 projects that will form GRAND's Phase 2 research project portfolio.

The next phase of the process will be the LOI Responses, which will be focused on filling these anticipated 20-25 project slots in the Phase 2 portfolio. More information about this next phase is included in the attached, along with your individual project feedback.  The LOI Responses template is available at http://grand-nce.ca/renewal/template-and-instructions-for-loi-responses.

If you have any questions or concerns, or if you require any additional information at this time, please let me know. Thanks.

Regards,
Adrian Sheppard
Director, Operations
GRAND NCE
Centre for Digital Media
685 Great Northern Way
Vancouver BC V5T 0C6
EOF;
        
        $from = "Adrian Sheppard <adrian_sheppard@gnwc.ca>";
        $filename = "{$loi_name}_Feedback-August2013.pdf";
        //$file = "data/review-feedback/{$type}/{$filename}";
        //$file_content = @file_get_contents($file);
        $admin = Person::newFromId(4);
        $report = new DummyReport("LOIFeedbackReportPDF", $admin, $loi);
        $check = $report->getPDF();
        
        if(count($check)>0){
            $sto = new ReportStorage($admin);
            $file_content = $sto->fetch_pdf($check[0]['token']);
        }
        else{
            $file_content = "";
        }

        $error_code = 0; //If all is good return 0;

        if($file_content){
            $success = ReviewResults::mail_attachment($file_content, $filename, $to, $from, $subject, $email_body);
            if($success){
                //Update the NI record that the email was sent out.
                $curr_year = REPORTING_YEAR;
                $query =<<<EOF
                UPDATE grand_review_results
                SET email_sent = 1
                WHERE user_id = {$loi_id} AND type = 'LOI' AND year = {$curr_year}
EOF;
                $result = DBFunctions::execSQL($query, true);
            }
            else{
                $error_code = 1; 
            }
        }
        else{
            $error_code = 2;
        }

        return $error_code;
    }

    static function emailPDF($ni_id, $type){
        global $wgUser, $wgMessage;
        $recipients = array();
        if($type == CNI || $type == PNI){
            $ni = Person::newFromId($ni_id);
            
            $recipients[] = $ni;
        }
        else if($type == "Project"){
            $ni = Project::newFromId($ni_id);
            $leaders = $ni->getLeaders();
            $coleaders = $ni->getCoLeaders();
            $recipients = array_merge($leaders, $coleaders);
        }
        
        $subject = "GRAND {$type} Allocations 2014-15";
        $ni_name = $ni->getName();
        
        foreach($recipients as $rec){
            $to = $rec->getEmail();
            $rec_name_good = $rec->getNameForForms();
            
            $email_body =<<<EOF
Dear {$rec_name_good},

Please find attached a PDF with your GRAND {$type} Research Funding Allocation for 2014-15, along with reviewer feedback from the Research Management Committee.

Best Regards,
Adrian Sheppard
EOF;

            $from = "Adrian Sheppard <adrian_sheppard@gnwc.ca>";
            $filename = "{$ni_name}.March2014.pdf";
            $file = "data/review-feedback/{$type}/{$filename}";
            $file_content = @file_get_contents($file);
            
            $error_code = 0; //If all is good return 0;

            if($file_content !== false){
                $success = ReviewResults::mail_attachment($file_content, $filename, $to, $from, $subject, $email_body);
                if($success){
                    //Update the NI record that the email was sent out.
                    $curr_year = REPORTING_YEAR;
                    $query =<<<EOF
                    UPDATE grand_review_results
                    SET email_sent = 1
                    WHERE user_id = {$ni_id} AND type = '{$type}' AND year = {$curr_year}
EOF;
                    $result = DBFunctions::execSQL($query, true);

                    //$wgMessage->addSuccess("Email has been sent successfully!");
                
                }
                else{
                    $error_code = max($error_code, 1); 
                    //$wgMessage->addError("There was a problem with sending the email.");
                }
            }
            else{
                $error_code = max($error_code, 2);
                //$wgMessage->addError("There was a problem with reading the PDF file.");
            }
        }
        exit;
        return $error_code;
    }
    
    static function mail_attachment($content, $filename, $to, $from, $subject, $message) {
        $to = "dwt@ualberta.ca";
        $content = chunk_split(base64_encode($content));
        $uid = md5(uniqid(time()));
        
        $header = "From: ".$from."\r\n";
        //$header .= "Cc: ".$cc."\r\n";
        $header .= "Reply-To: ".$from."\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= $message."\r\n\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
        $header .= "--".$uid."--";
        if (mail($to, $subject, "", $header)) {
            return true;
        } else {
            return false;
        }
    }

    static function handleSubmit(){
        global $wgUser, $wgMessage;

        $my_id = $wgUser->getId();
        
        //var_dump($_POST);
        //exit;
        $type = (isset($_POST['ni_type']))? $_POST['ni_type'] : "";
        $year = (isset($_POST['year']))? $_POST['year'] : "";
        $nis = (isset($_POST['ni']))? $_POST['ni'] : array();

        if(!empty($type) && !empty($year) && !empty($nis)){
            
            foreach($nis as $ni_id => $ni_data){
                $allocated_amount = (isset($ni_data['allocated_amount']) && !empty($ni_data['allocated_amount'])) ? $ni_data['allocated_amount'] : 0;
                $allocated_amount2 = (isset($ni_data['allocated_amount2']) && !empty($ni_data['allocated_amount2'])) ? $ni_data['allocated_amount2'] : 0;
                $allocated_amount3 = (isset($ni_data['allocated_amount3']) && !empty($ni_data['allocated_amount3'])) ? $ni_data['allocated_amount3'] : 0;
                
                $overall_score = (isset($ni_data['overall_score'])) ? $ni_data['overall_score'] : "";
                $send_email = (isset($ni_data['send_email'])) ? 1 : 0;
                
                $allocated_amount = mysql_real_escape_string(floatval(str_replace(",", "", $allocated_amount)));
                $allocated_amount2 = mysql_real_escape_string(floatval(str_replace(",", "", $allocated_amount2)));
                $allocated_amount3 = mysql_real_escape_string(floatval(str_replace(",", "", $allocated_amount3)));
                $overall_score = mysql_real_escape_string($overall_score);

                $query =<<<EOF
                INSERT INTO grand_review_results (user_id, type, year, allocated_amount, allocated_amount2, allocated_amount3, overall_score, send_email)
                VALUES ({$ni_id}, '{$type}', {$year}, '{$allocated_amount}', '{$allocated_amount2}', '{$allocated_amount3}', '{$overall_score}', '{$send_email}')
                ON DUPLICATE KEY UPDATE
                allocated_amount = '{$allocated_amount}',
                allocated_amount2 = '{$allocated_amount2}',
                allocated_amount3 = '{$allocated_amount3}',
                overall_score = '{$overall_score}',
                send_email = '{$send_email}'
EOF;
                $result = DBFunctions::execSQL($query, true);
            }
        }
        else{
            $result = false;
        }
        
        if($result){
            $wgMessage->addSuccess("{$type} Review Results updated successfully!");
        }
        else{
            $wgMessage->addError("There was a problem with saving {$type} Review Results. Please contact support if the problem persists.");
        }
    }

    static function getData($blob_type, $rptype, $question, $sub, $eval_id=0, $evalYear=EVAL_YEAR, $proj_id=0){

        $addr = ReportBlob::create_address($rptype, SEC_NONE, $question, $sub->getId());
        $blb = new ReportBlob($blob_type, $evalYear, $eval_id, $proj_id);
        
        $data = "";
       
        $result = $blb->load($addr);
        
        $data = $blb->getData();
        
        return $data;
    }

    static function generateAllFeedback($type='PNI'){
        global $wgUser;

        if($type == 'LOI'){
            $lois = LOI::getAssignedLOIs(REPORTING_YEAR);
            foreach ($lois as $loi){
                //$loi_id = $loi->getId();
                $admin = Person::newFromId(4);
                $report = new DummyReport("LOIFeedbackReportPDF", $admin, $loi);
                $report->generatePDF(null, false);
                //break;
            }
        }
        else{
            if($type == PNI || $type == CNI){
                $nis = Person::getAllEvaluates($type); //Person::getAllPeopleDuring($type, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");
            }
            else if($type == "Project"){
                $nis = array();
                $projects = Project::getAllProjects();
                foreach($projects as $project){
                    if($project->getPhase() == 2){
                        $nis[$project->getName()] = $project;
                    }
                }
            }

            foreach ($nis as $ni) {
                $ni_id = $ni->getId();

                ReviewResults::generateFeedback($ni_id, $type);
                echo $ni->getName() ."<br />";
            }
        }
    }

    static function generateFeedback($ni_id, $type="PNI"){
        global $wgOut;

        $wgOut->clearHTML();
        if($type == PNI || $type == CNI){
            $ni = Person::newFromId($ni_id);
            $name = $ni->getNameForForms();
            $university = $ni->getUni();
        }
        else if($type == "Project"){
            $ni = Project::newFromId($ni_id);
            $name = $ni->getName();
            $leaders = $ni->getLeaders();
            $coleaders = $ni->getCoLeaders();
        }
        $curr_year = REPORTING_YEAR;
        $boilerplate = "";
        if($type == "PNI"){
            $rtype = RP_EVAL_RESEARCHER;
            $boilerplate = "<p>There were 49 prospective PNIs evaluated in this review cycle, along with an additional 11 CNIs who were proposed as project co-leaders.  Funding allocations for 2014-15 were made in four tiers:  Top Tier: $65,000; Upper Middle: $55,000; Lower Middle: $45,000; and Bottom Tier: $35,000.  Note that a researcher was not awarded more than his/her budget request, notwithstanding their funding tier.</p>
<br />
<p>Of the 49 prospective PNIs evaluated, 46 were funded as PNIs: 21 were Top Tier, 19 were Upper Middle, 6 were Lower Middle, and 3 were not funded as PNIs.  Of the 11 CNIs who were proposed as project co-leaders, they were evaluated across all four tiers, although they were only eligible for funding at the $45,000 and $35,000 levels.  Six were funded at the $45,000 level, four were funded at the $35,000 level, and one was awarded no funding.  Again, a researcher was not awarded more than his/her budget request, notwithstanding their funding tier.</p>
<br />
<p>The \"2015 Allocation\" amounts are full year allocations based on a proposed \"Phase 2\" research budget.  The \"April-December 2014 Amount\" is your actual allocation for the funding period from 01Apr2014 through 31Dec2014.  These amounts are notably lower than the \"2015 Allocation\" for two reasons: (1) they have been scaled down to fit within the Phase 1 research budget, which is less than the proposed Phase 2 research budget, and (2) they are providing funding for only a nine-month period.  If GRAND is renewed at a funding level consistent with the proposed Phase 2 research budget, then additional allocations of research funding for 01Jan2015 – 31Mar2015 are expected to be a full 25% of the 2015 Allocation indicated.</p>
<br />
<p>Project Leaders and co-leaders will be receiving separate feedback from the RMC regarding their projects which will also include details regarding project funding available for CNIs.</p>
<br />
<p>Each PNI and CNI who is a project co-leader was reviewed by at least two RMC evaluators.  The Overall Rating is based not only on the reviewer scores, but also on the discussion at the RMC meeting.  The available scores are as follows:  1) Overall Score:  Top, Upper Middle, Lower Middle, Bottom; 2) Each of the 5 NCE Evaluation Criteria and Rating for Quality of Report: Exceptional, Very Good, Satisfactory, Unsatisfactory; and 3) Confidence Level of Evaluator: Very High, High, Moderate, Low.</p>";
        }
        else if($type == "CNI"){
            $rtype = RP_EVAL_CNI;
            $boilerplate = "<p>There were 49 prospective PNIs evaluated in this review cycle, along with an additional 11 CNIs who were proposed as project co-leaders.  Funding allocations for 2014-15 were made in four tiers:  Top Tier: $65,000; Upper Middle: $55,000; Lower Middle: $45,000; and Bottom Tier: $35,000.  Note that a researcher was not awarded more than his/her budget request, notwithstanding their funding tier.</p>
<br />
<p>Of the 49 prospective PNIs evaluated, 46 were funded as PNIs: 21 were Top Tier, 19 were Upper Middle, 6 were Lower Middle, and 3 were not funded as PNIs.  Of the 11 CNIs who were proposed as project co-leaders, they were evaluated across all four tiers, although they were only eligible for funding at the $45,000 and $35,000 levels.  Six were funded at the $45,000 level, four were funded at the $35,000 level, and one was awarded no funding.  Again, a researcher was not awarded more than his/her budget request, notwithstanding their funding tier.</p>
<br />
<p>The \"2015 Allocation\" amounts are full year allocations based on a proposed \"Phase 2\" research budget.  The \"April-December 2014 Amount\" is your actual allocation for the funding period from 01Apr2014 through 31Dec2014.  These amounts are notably lower than the \"2015 Allocation\" for two reasons: (1) they have been scaled down to fit within the Phase 1 research budget, which is less than the proposed Phase 2 research budget, and (2) they are providing funding for only a nine-month period.  If GRAND is renewed at a funding level consistent with the proposed Phase 2 research budget, then additional allocations of research funding for 01Jan2015 – 31Mar2015 are expected to be a full 25% of the 2015 Allocation indicated.</p>
<br />
<p>Project Leaders and co-leaders will be receiving separate feedback from the RMC regarding their projects which will also include details regarding project funding available for CNIs.</p>
<br />
<p>Each PNI and CNI who is a project co-leader was reviewed by at least two RMC evaluators.  The Overall Rating is based not only on the reviewer scores, but also on the discussion at the RMC meeting.  The available scores are as follows:  1) Overall Score:  Top, Upper Middle, Lower Middle, Bottom; 2) Each of the 5 NCE Evaluation Criteria and Rating for Quality of Report: Exceptional, Very Good, Satisfactory, Unsatisfactory; and 3) Confidence Level of Evaluator: Very High, High, Moderate, Low.</p>";
        }
        else if($type == "Project"){
            $rtype = RP_EVAL_PROJECT;
            $boilerplate = "<p>There were 23 projects evaluated in this review cycle.  Each project was reviewed by three RMC evaluators.  The highest scoring project received three \"Top Tier\" overall ratings, and the lowest scoring project received three \"Bottom Tier\" ratings.  However all but a few projects received at least one \"Upper Middle\" overall rating.  There were 22 standard projects and one \"alliance\" project.  Alliance projects are funded from a separate pool of funds.  Of the 22 standard projects, 19 were deemed ready to proceed as full projects, with the remaining three to proceed on a development basis with reduced funding and additional oversight.</p>
<br />
<p>The funding level for each project was impacted significantly by the level of PNI investment in the project.  The goal across the research funding allocations for standard projects is for two-thirds of the funding to go to PNIs and the remaining one-third to go to CNIs.  Based on the RMC allocations to PNIs, the level of PNI investment in that project was determined based on the percentage of that PNI's funding request that was designated for that project.  The presumptive amount that project will then have for CNIs will be 50% of the amount of that PNI investment.  Although some adjustments were made to address some outliers, in most cases the application of this formula led to a reasonable result.</p>
<br />
<p>For CNIs who are project co-leaders, their RMC allocations were also applied to their projects based on the percentage of that CNI's funding request that was designated for that project.  These amounts are deducted from the overall CNI allocation for the project.  The balance of the CNI allocation for the project will be distributed among the remaining CNIs on the project at the direction of the Project Leader and Co-Leader.</p>
<br />
<p>Project Leaders and co-leaders will be receiving shortly a separate document that provides the details of their overall project funding for 2014-15.</p>
<br />
<p>For the reviewer scoring of projects, the available scores are as follows:  1) Overall Score:  Top, Upper Middle, Lower Middle, Bottom; 2) Each of the 4 NCE Evaluation Criteria and Rating for Quality of Report: Exceptional, Very Good, Satisfactory, Unsatisfactory; and 3) Confidence Level of Evaluator: Very High, High, Moderate, Low.</p>";
        }

        $query = "SELECT * FROM grand_review_results WHERE year='{$curr_year}' AND user_id='{$ni_id}' AND type = '{$type}'";
        $data = DBFunctions::execSQL($query);
        
        $allocated_amount = $allocated_amount2 = $overall_score = "";
        if(count($data) > 0){
            $row = $data[0];
            $allocated_amount = $row['allocated_amount'];
            $allocated_amount2 = $row['allocated_amount2'];
            $overall_score = $row['overall_score']; 
        }

        setlocale(LC_MONETARY, 'en_CA');
        $allocated_amount = @money_format('%i', $allocated_amount);
        $allocated_amount2 = @money_format('%i', $allocated_amount2);
        $allocated_amount3 = @money_format('%i', $allocated_amount3);
        
        $allocated_html = "";
        if($type == PNI || $type == CNI){
            // NI Specific HTML variables
            $title = "GRAND NCE - Research Management Committee - Network Investigator Review 2014 - Feedback";
            $person_html = <<<EOF
                <tr>
                    <td align='right'><strong>Name:</strong></td>
                    <td>{$name}</td>
                </tr>
                <tr>
                    <td align='right'><strong>University:</strong></td>
                    <td>{$university}</td>
                </tr>
EOF;
            $allocated_html = <<<EOF
                <tr>
                    <td align='right'><strong>2015 Allocation:</strong></td>
                    <td>{$allocated_amount2}</td>
                </tr>
                <tr>
                    <td align='right'><strong>Apr-Dec 2014 Amount:</strong></td>
                    <td>{$allocated_amount}</td>
                </tr>
                <tr>
                    <td align='right'><strong>Overall Rating (Tier):</strong></td>
                    <td>{$overall_score}</td>
                </tr>
EOF;
            $sections = array(
                "Overall Score" => array(EVL_OVERALLSCORE, 0),
                "Excellence of Research Program" => array(EVL_EXCELLENCE, EVL_EXCELLENCE_COM),
                "Development of HQP" => array(EVL_HQPDEVELOPMENT, EVL_HQPDEVELOPMENT_COM),
                "Networking and Partnerships" => array(EVL_NETWORKING, EVL_NETWORKING_COM),
                "Knowledge and Technology Exchange and Exploitation" => array(EVL_KNOWLEDGE, EVL_KNOWLEDGE_COM),
                "Management of the Network" => array(EVL_MANAGEMENT, EVL_MANAGEMENT_COM),
                "Rating for Quality of Report" => array(EVL_REPORTQUALITY, EVL_REPORTQUALITY_COM),
                "Evaluator Comments" => array(0, EVL_OTHERCOMMENTS),
                "Confidence Level of Evaluator" => array(EVL_CONFIDENCE, 0)
            );

            $evaluators = $ni->getEvaluators($type, 2013);
        }
        else if($type == "Project"){
            // Project Specific HTML variables
            $title = "GRAND NCE - Research Management Committee - Phase2 Project Review 2014 - Feedback";
            $leader_names = array();
            $coleader_names = array();
            
            foreach($leaders as $leader){
                $leader_names[] = $leader->getNameForForms()." ({$leader->getUni()})";
            }
            foreach($coleaders as $leader){
                $coleader_names[] = $leader->getNameForForms()." ({$leader->getUni()})";
            }
            $lead_names = implode("<br />", $leader_names);
            $colead_names = implode("<br />", $coleader_names);
            $person_html = <<<EOF
            <tr>
                <td align='right'><strong>Project Name:</strong></td>
                <td>{$name} - {$ni->getFullName()}</td>
            </tr>
            <tr>
                <td align='right'><strong>Leader(s):</strong></td>
                <td>{$lead_names}</td>
            </tr>
            <tr>
                <td align='right'><strong>Co-Leader(s):</strong></td>
                <td>{$colead_names}</td>
            </tr>
EOF;
            $allocated_html = <<<EOF
                <tr>
                    <td align='right'><strong>Overall Rating (Tier):</strong></td>
                    <td>{$overall_score}</td>
                </tr>
EOF;
            $sections = array(
                "Overall Score" => array(EVL_OVERALLSCORE, 0),
                "Excellence of Research Program" => array(EVL_EXCELLENCE, EVL_EXCELLENCE_COM),
                "Development of HQP" => array(EVL_HQPDEVELOPMENT, EVL_HQPDEVELOPMENT_COM),
                "Networking and Partnerships" => array(EVL_NETWORKING, EVL_NETWORKING_COM),
                "Knowledge and Technology Exchange and Exploitation" => array(EVL_KNOWLEDGE, EVL_KNOWLEDGE_COM),
                "Rating for Quality of Report" => array(EVL_REPORTQUALITY, EVL_REPORTQUALITY_COM),
                "Evaluator Comments" => array(0, EVL_OTHERCOMMENTS),
                "Confidence Level of Evaluator" => array(EVL_CONFIDENCE, 0)
            );

            $evaluators = $ni->getEvaluators(2013);
        }
        
        $html =<<<EOF
        <style type="text/css">
        td {
            vertical-align: top;
        }
        </style>
        <div>
            <h2>{$title}</h2>
            <table>
                {$person_html}
                {$allocated_html}
            </table>
        </div>
        <div>
        <h3>Description of Overall Process and Results</h3>
        <p>{$boilerplate}</p>
        </div>
EOF;

        // Do an initial pass of the evaluators to see which have not done anything
        foreach($evaluators as $key => $eval){
            $found = false;
            foreach ($sections as $sec_name => $sec_addr){
                $ev_id = $eval->getId();
                if($type == PNI || $type == CNI){
                    $score = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[0], $ni, $ev_id, $curr_year);
                    $comments = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[1], $ni, $ev_id, $curr_year);
                }
                else if($type == "Project"){
                    $score = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[0], $ni, $ev_id, $curr_year, $ni);
                    $comments = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[1], $ni, $ev_id, $curr_year, $ni);
                }
                
                if($score != null || $comments != null){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                unset($evaluators[$key]);
            }
        }
        //now loop through all questions and evaluators and get the data
        foreach ($sections as $sec_name => $sec_addr){
            $score_html = ($sec_addr[0] != 0) ? "<th align='left'>Score</th>" : "";
            $comment_html = ($sec_addr[1] != 0) ? "<th align='left'>Comment</th>" : "<th>&nbsp;</th>";
            $html .=<<<EOF
            <h3>{$sec_name}</h3>
            <table cellpadding="4" style="margin-bottom:15px;" width="100%" align="left;">
                <tr><th>&nbsp;</th>{$score_html}{$comment_html}</tr>
EOF;
            $ev_count = 1;

            $rows = array();
            foreach($evaluators as $eval){
                $ev_id = $eval->getId();
                if($type == PNI || $type == CNI){
                    $score = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[0], $ni, $ev_id, $curr_year);
                    $comments = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[1], $ni, $ev_id, $curr_year);
                }
                else if($type == "Project"){
                    $score = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[0], $ni, $ev_id, $curr_year, $ni);
                    $comments = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[1], $ni, $ev_id, $curr_year, $ni);
                }
                
                if(isset($score['revised']) && !empty($score['revised'])){
                    $score = $score['revised'];
                }else{
                    $score = $score['original'];
                }
                
                if(is_array($comments)){
                    if(isset($comments['revised']) && !empty($comments['revised'])){
                        $comments = $comments['revised'];
                    }else{
                        $comments = $comments['original'];
                    }
                }
                else{
                    $comments = array();
                }
                $coms = array();
                if(is_array($comments)){
                    foreach($comments as $com){
                        $coms[] = substr($com, 2);
                    }
                    $comments = implode("<br />", $coms);
                }
                $comments = nl2br($comments);
                if($ev_id == 11){ // K.S.Booth
                    $score = "";
                }
                if($score != "" || $comments != ""){
                    $score_cell = ($sec_addr[0] != 0) ? "<td width='13%'>{$score}</td>" : "";
                    $comment_cell = ($sec_addr[1] != 0) ? "<td>{$comments}</td>" : "<td>&nbsp;</td>";
                    if($ev_id == 11){ // K.S.Booth
                        $rows[100+$ev_count] = <<<EOF
                        <tr>
                        <td width="11%"><strong>Additional RMC Comments</strong></td>
                        $score_cell
                        $comment_cell
                        </tr>
EOF;
                        $ev_count--;
                    }
                    else{
                        $rows[$ev_count] = <<<EOF
                        <tr>
                        <td width="11%"><strong>RMC{$ev_count}</strong></td>
                        $score_cell
                        $comment_cell
                        </tr>
EOF;
                    }
                }
                $ev_count++;
            }
            if($type == "Project" && $sec_addr[1] == EVL_OTHERCOMMENTS){
                $isac = Person::getAllPeople(ISAC);
                $isacN = 1;
                foreach($isac as $i => $person){
                    $addr = ReportBlob::create_address(RP_ISAC, ISAC_PHASE2, ISAC_PHASE2_COMMENT, 0);
                    $blb = new ReportBlob(BLOB_TEXT, 2013, $person->getId(), $ni_id);
                    $result = $blb->load($addr);
                    $data = $blb->getData();
                    if($data != null){
                        $comment_cell = "<td>$data</td>";
                        $rows[1000+$isacN] = <<<EOF
                        <tr>
                        <td width="11%"><strong>ISAC{$isacN}</strong></td>
                        $comment_cell
                        </tr>
EOF;
                        $isacN++;
                    }
                }
            }
            ksort($rows);
            $html .= implode("", $rows);
            $html .=<<<EOF
            </table>
            <br />
EOF;
        }
        
        //echo $html;

        $pdf = "";
        try {
            $pdf = PDFGenerator::generate("Report" , $html, "", null, false);
            $filename = $ni->getName();
            $filename .= ".March2014";
            //var_dump($pdf);
            file_put_contents("data/review-feedback/{$type}/{$filename}.pdf", $pdf['pdf']);
        }
        catch(DOMPDF_Internal_Exception $e){
            echo "ERROR!!!";
            echo $e->getMessage();
            // TODO: Display a nice message to the user if the generation failed
        }
        //PDFGenerator::stream($pdfStr);
        //$pdf = $pdf->output();
    }

    static function reviewResults($type){
        global $wgOut, $wgScriptPath, $wgServer, $wgUser;

        $my_id = $wgUser->getId();
        $me = Person::newFromId($wgUser->getId());
        
        if($type != "CNI" && $type != "PNI" && $type != "Project"){
            $type = "PNI";
        }

        $curr_year = REPORTING_YEAR;
        if($type == PNI || $type == CNI){
            $nis = Person::getAllEvaluates($type); //Person::getAllPeopleDuring($type, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");

            $nis_sorted = array();
            foreach ($nis as $ni){
                $ni_rev_name = $ni->getReversedName();
                $nis_sorted[$ni_rev_name] = $ni;
            }
            ksort($nis_sorted);
        }
        else if($type == "Project"){
            $nis = array();
            $projects = Project::getAllProjects();
            foreach($projects as $project){
                if($project->getPhase() == 2){
                    $nis[$project->getName()] = $project;
                }
            }
            $nis_sorted = $nis;
            ksort($nis_sorted);
        }

        $query = "SELECT * FROM grand_review_results WHERE year={$curr_year} AND type='{$type}'";
        $data = DBFunctions::execSQL($query);

        $fetched = array();
        foreach($data as $row){
            $id = $row['user_id'];
            $fetched[$id] = array('allocated_amount'    => $row['allocated_amount'], 
                                  'allocated_amount2'   => $row['allocated_amount2'],
                                  'allocated_amount3'   => $row['allocated_amount3'],
                                  'overall_score'       => $row['overall_score'],
                                  'send_email'          => $row['send_email'], 
                                  'email_sent'          => $row['email_sent']);    
        }

        if($type == PNI || $type == CNI){
            $allocationHeadCells = <<<EOF
            <th width="15%">2014 Allocation</th>
            <th width="15%">2015 Allocation</th>
EOF;
        }
        else if($type == "Project"){
            $allocationHeadCells = <<<EOF
            <th width="10%">2014 PNI Allocation</th>
            <th width="10%">2014 CNI Allocation</th>
            <th width="10%">2015 Amount</th>
EOF;
        }

        $html =<<<EOF
            <h3>RMC Review Results ({$type})</h3>
            <form id="resultsForm" action='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}#{$type}' method='post'>
            
            <table width='97.5%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
            <tr>
            <th>NI Name</th>
            $allocationHeadCells
            <th width="15%">Overall Score</th>
            <th width="15%">Feedback PDF</th>
            <th width="15%">Send Email?</th>
            <th width="15%">Email</th>
            </tr>
EOF;
            foreach ($nis_sorted as $ni_name => $ni) {
                $ni_id = $ni->getId();
                $filename = $ni->getName();

                //$ni_name = $ni->getNameForForms();
                $allocated_amount = "";
                $allocated_amount2 = "";
                $allocated_amount3 = "";
                $overall_score = "";
                $send_email_checked = "checked='checked'";
                $email_sent = "Email&nbsp;Not&nbsp;Sent";
                $email_sent_bg = "background-color: red;";
                if(isset($fetched[$ni_id])){
                    $allocated_amount = (isset($fetched[$ni_id]['allocated_amount'])) ? $fetched[$ni_id]['allocated_amount'] : "";
                    $allocated_amount2 = (isset($fetched[$ni_id]['allocated_amount2'])) ? $fetched[$ni_id]['allocated_amount2'] : "";
                    $allocated_amount3 = (isset($fetched[$ni_id]['allocated_amount3'])) ? $fetched[$ni_id]['allocated_amount3'] : "";
                    $overall_score = (isset($fetched[$ni_id]['overall_score'])) ? $fetched[$ni_id]['overall_score'] : "";
                    if(isset($fetched[$ni_id]['send_email']) && $fetched[$ni_id]['send_email'] == 0){
                        $send_email_checked = "";
                    }

                    if(isset($fetched[$ni_id]['email_sent']) && $fetched[$ni_id]['email_sent'] == 1){
                        $email_sent = "Email&nbsp;Sent";
                        $email_sent_bg = "background-color: green;";
                    }
                }
                if(file_exists("data/review-feedback/{$type}/{$filename}.March2014.pdf")){
                    $file_link = "<a href='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}&getPDF={$filename}' target='_blank'>Download</a>"; 
                }else{
                    $file_link = "No&nbsp;PDF&nbsp;found";
                }
                
                if($type == PNI || $type == CNI){
                    $allocationCells = <<<EOF
                    <td><input style='width:175px;' type="text" name="ni[{$ni_id}][allocated_amount]" value="{$allocated_amount}" class="number" /></td>
                    <td><input style='width:175px;' type="text" name="ni[{$ni_id}][allocated_amount2]" value="{$allocated_amount2}" class="number" /></td>
EOF;
                }
                else if($type == "Project"){
                    $allocationCells = <<<EOF
                    <td><input style='width:175px;' type="text" name="ni[{$ni_id}][allocated_amount]" value="{$allocated_amount}" class="number" /></td>
                    <td><input style='width:175px;' type="text" name="ni[{$ni_id}][allocated_amount2]" value="{$allocated_amount2}" class="number" /></td>
                    <td><input style='width:175px;' type="text" name="ni[{$ni_id}][allocated_amount3]" value="{$allocated_amount3}" class="number" /></td>
EOF;
                }
                $html .=<<<EOF
                <tr>
                <td>{$ni_name}</td>
                $allocationCells
                <td><input type="text" name="ni[{$ni_id}][overall_score]" value="{$overall_score}" /></td>
                <td align="center">{$file_link}</td>
                <td align="center"><input type='checkbox' name='ni[{$ni_id}][send_email]' {$send_email_checked} /></td>
                <td align="center"><span style="padding:5px; {$email_sent_bg}">{$email_sent}</span></td>
                </tr>
EOF;
            }

            $html .=<<<EOF
            </table>
            <br />
            <input type='hidden' name='ni_type' value='{$type}' />
            <input type='hidden' name='year' value='{$curr_year}' />
            <input type='submit' name='submit' value='Submit' />
            <input type='submit' name='submit' value='Send out Emails' disabled />
            </form>
EOF;
        return $html;
    }

    static function reviewLOIResults(){
        global $wgOut, $wgScriptPath, $wgServer, $wgUser;

        $my_id = $wgUser->getId();
        $me = Person::newFromId($wgUser->getId());
        
        $type = "LOI";

        $curr_year = REPORTING_YEAR;
        $lois = LOI::getAssignedLOIs($curr_year);

        $query = "SELECT * FROM grand_review_results WHERE year={$curr_year} AND type='{$type}'";
        $data = DBFunctions::execSQL($query);

        $fetched = array();
        foreach($data as $row){
            $id = $row['user_id'];
            $fetched[$id] = array('email_sent'=>$row['email_sent']);    
        }

        $html =<<<EOF
            <h3>RMC Review Results ({$type})</h3>
            <form id="resultsForm" action='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}#loi' method='post'>
            
            <table width='90%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
            <tr>
            <th>LOI Name</th>
            <th width="30%">Leader Name</th>
            <th width="30%">co-Leader Name</th>
            <th width="15%">Feedback PDF</th>
            <th width="15%">Email</th>
            </tr>
EOF;
            foreach ($lois as $loi) {
                $loi_id = $loi->getId();
                $loi_name = $loi->getName();
                $filename = $loi->getName();
                $lead = $loi->getLeadEmail();
                if(!empty($lead['email'])){
                    $lead_email = "<a href='mailto:".$lead['email']."'>".$lead['name']."</a>";
                }
                else{
                    $lead_email = $lead['name'];
                }

                $colead = $loi->getCoLeadEmail();    
                if(!empty($colead['email'])){
                    $colead_email = "<a href='mailto:".$colead['email']."'>".$colead['name']."</a>";
                }
                else{
                    $colead_email = $colead['name'];
                }
                $email_sent = "Email Not Sent";
                $email_sent_bg = "background-color: red;";
                if(isset($fetched[$loi_id])){
                    if(isset($fetched[$loi_id]['email_sent']) &&  $fetched[$loi_id]['email_sent'] == 1){
                        $email_sent = "Email Sent";
                        $email_sent_bg = "background-color: green;";
                    }
                }

                $admin = Person::newFromId(4); //Just because I need to pass a person object
                $report = new DummyReport("LOIFeedbackReportPDF", $admin, $loi);
                $check = $report->getPDF();
                if(count($check) > 0){
                    $tok = $check[0]['token'];
                    $downloadButton = "<a id='download{$loi_id}' target='downloadIframe' class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok'>Download</a>";
                }
                else{
                    $downloadButton = "No PDF found";
                }
                
                $html .=<<<EOF
                <tr>
                <td>{$loi_name}</td>
                <td>{$lead_email}</td>
                <td>{$colead_email}</td>
                <td align="center">{$downloadButton}</td>
                <td align="center"><span style="padding:5px; {$email_sent_bg}">{$email_sent}</span></td>
                </tr>
EOF;
            }

            $html .=<<<EOF
            </table>
            <br />
            <input type='hidden' name='ni_type' value='{$type}' />
            <input type='hidden' name='year' value='{$curr_year}' />
            
            <input type='submit' name='submit' value='Send out Emails' disabled />
            </form>
EOF;
        return $html;
    }
}

?>
