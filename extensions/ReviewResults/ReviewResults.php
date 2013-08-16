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
	    else if(!empty($_GET['type']) && $_GET['type'] == 'LOI'){
	    	$type = "LOI";
	    }
	    
	    if($type == 'LOI'){
	    	ReviewResults::loi_routine();
	    }
	    else{
		    ReviewResults::ni_routine();
		}
	}

	static function ni_routine(){
		global $wgUser, $wgOut, $wgServer, $wgScriptPath;

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
	    	$filename = $_GET['getPDF'] .".March2013.pdf";
	    	$file = "/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}";
	    	if(file_exists($file)){
		    	header('Content-type: application/pdf');
				header('Content-Disposition: inline; filename="' . $filename . '"');
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: ' . filesize($file));
				header('Accept-Ranges: bytes');

				@readfile($file);
			}
	    }
	    else if(isset($_GET['emailPDF'])){
	    	$ni_id = $_GET['emailPDF'];
	    	ReviewResults::emailPDF($ni_id, $type);
	    }
	    

	    ReviewResults::reviewResults($type);
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
	  //   else if(isset($_GET['getPDF'])){
	  //   	$filename = $_GET['getPDF'] .".March2013.pdf";
	  //   	$file = "/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}";
	  //   	if(file_exists($file)){
		 //    	header('Content-type: application/pdf');
			// 	header('Content-Disposition: inline; filename="' . $filename . '"');
			// 	header('Content-Transfer-Encoding: binary');
			// 	header('Content-Length: ' . filesize($file));
			// 	header('Accept-Ranges: bytes');

			// 	@readfile($file);
			// }
	  //   }
	    //else if(isset($_GET['emailPDF'])){
	    //	$ni_id = $_GET['emailPDF'];
	    //	ReviewResults::emailPDF($ni_id, $type);
	    //}
	    

	    ReviewResults::reviewLOIResults();
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

		$query =<<<EOF
		SELECT * FROM grand_review_results
		WHERE year={$curr_year} AND type = 'LOI' AND user_id=%d
EOF;
	
		$sent_success = array();
		$sent_fail = array();
		$file_fail = array();

		
	    foreach($lois as $loi){
	    	$loi_id = $loi->getId();
	    	$sql = sprintf($query, $loi_id);
	    	$data = DBFunctions::execSQL($query);
	    	if(count($data)>0 && $data[0]['email_sent'] == 1){
	    		continue;
	    	}

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

		$to = "dgolovan@gmail.com";//$ni_email; 
		$subject = "GRAND LOI Evaluations 2013-14";
		
		$email_body =<<<EOF
Dear {$loi_name},

Please find attached a PDF with your GRAND LOI Research Funding Allocation for 2013-14, along with reviewer feedback from the Research Management Committee.

Best Regards,
Adrian Sheppard
EOF;
		
		$from = "Adrian Sheppard <adrian_sheppard@gnwc.ca>";
		$filename = "{$loi_name}.August2013.pdf";
		//$file = "/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}";
		//$file_content = @file_get_contents($file);
		$report = new DummyReport("LOIFeedbackReportPDF", Person::newFromId(4), $loi);
		$check = $report->getPDF();

		$file_content = isset($check['pdf'])? $check['pdf'] : "";

		$error_code = 0; //If all is good return 0;

		if($file_content !== false){
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
		$ni = Person::newFromId($ni_id);
		$ni_name = $ni->getName();
		$ni_email = $ni->getEmail();
		$ni_name_good = $ni->getNameForForms();

		$to = $ni_email; 
		$subject = "GRAND PNI Allocations 2013-14";
		
		$email_body =<<<EOF
Dear {$ni_name_good},

Please find attached a PDF with your GRAND {$type} Research Funding Allocation for 2013-14, along with reviewer feedback from the Research Management Committee.

Best Regards,
Adrian Sheppard
EOF;
		
		$from = "Adrian Sheppard <adrian_sheppard@gnwc.ca>";
		$filename = "{$ni_name}.March2013.pdf";
		$file = "/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}";
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
				$error_code = 1; 
				//$wgMessage->addError("There was a problem with sending the email.");
			}
		}
		else{
			$error_code = 2;
			//$wgMessage->addError("There was a problem with reading the PDF file.");
		}

		return $error_code;
	}
	
	static function mail_attachment($content, $filename, $to, $from, $subject, $message) {

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
				$allocated_amount = (isset($ni_data['allocated_amount']))? $ni_data['allocated_amount'] : 0;
				if(isset($ni_data['allocated_amount']) && !empty($ni_data['allocated_amount'])){
					$allocated_amount = $ni_data['allocated_amount'];
				}
				else{
					$allocated_amount = 0;
				}
				$overall_score = (isset($ni_data['overall_score']))? $ni_data['overall_score'] : "";
				//if(empty($ni_data['allocated_amount']) && empty($ni_data['overall_score'])){
				//	continue;
				//}

				$query =<<<EOF
				INSERT INTO grand_review_results (user_id, type, year, allocated_amount, overall_score)
				VALUES ({$ni_id}, '{$type}', {$year}, {$allocated_amount}, '{$overall_score}')
				ON DUPLICATE KEY UPDATE
				allocated_amount = {$allocated_amount},
				overall_score = '{$overall_score}'
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
    			break;

    		}
    	}
    	else{
    		$nis = Person::getAllEvaluates($type); //Person::getAllPeopleDuring($type, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");

	    	foreach ($nis as $ni) {
	    		$ni_id = $ni->getId();

	    		ReviewResults::generateFeedback($ni_id, $type);
	    		echo $ni->getNameForForms() ."<br />";
	    	}
    	}
    }


	static function generateFeedback($ni_id, $type="PNI"){
		global $wgOut;

		$wgOut->clearHTML();

		$ni = Person::newFromId($ni_id);
		$curr_year = REPORTING_YEAR;
		$boilerplate = "";
		if($type == "PNI"){
			$rtype = RP_EVAL_RESEARCHER;
			$boilerplate = "There were 62 PNIs evaluated in this review cycle. Funding allocations for 2013-14 were made in four tiers:  Top Tier: $50,000 (17 PNIs); Upper Middle Tier: $40,000 (26 PNIs); Lower Middle Tier: $30,000 (12 PNIs); and Bottom Tier: $25,000 (3 PNIs plus an additional 4 PNIs who requested $25K or less). Note that while the total amount of research funding allocated to PNIs is the same for 2013-14 as it was for 2012-13, the funding levels for the upper tiers are slightly lower, largely as a result of more PNIs achieving higher scores from reviewers. As more researchers gravitated toward the upper tiers, a fixed budget required the funding amounts corresponding to those tiers to be reduced accordingly.";
		}
		else if($type == "CNI"){
			$rtype = RP_EVAL_CNI;
			$boilerplate = "There were 80 CNIs evaluated in this review cycle. Some CNIs had two reviewers and others had only one reviewer; this was determined based on whether the amount of funding received in 2012-13 met a certain threshold amount. Funding allocations are based on the scores from the review, in conjunction with the funding request and the amount awarded for 2012-13. There was a limit placed on the incremental increase a CNI could be awarded over the previous year. The largest amount of funding awarded to any CNI for 2013-14 was $25,000 (9 CNIs). The total amount of research funding allocated to CNIs for 2013-14 is the same as it was for 2012-13.";
		}

		$query = "SELECT * FROM grand_review_results WHERE year={$curr_year} AND user_id={$ni_id}";
		$data = DBFunctions::execSQL($query);
		
		$allocated_amount = $overall_score = "";
		if(count($data) > 0){
            $row = $data[0];
            $allocated_amount = $row['allocated_amount'];
            $overall_score = $row['overall_score']; 
        }

        $name = $ni->getNameForForms();
       	$university = $ni->getUni();

       	setlocale(LC_MONETARY, 'en_CA');
		$allocated_amount = money_format('%i', $allocated_amount);
        $html =<<<EOF
        <style type="text/css">
        td {
			vertical-align: top;
		}
        </style>
        <div>
        <h2>GRAND 2013 Network Investigator Review</h2>
        <strong>Name:</strong> {$name}<br />
        <strong>University:</strong> {$university}<br />
        <strong>2013-14 Allocation:</strong> {$allocated_amount}
        </div>
        <div>
        <h3>Description of Overall Process and Results:</h3>
        <p>{$boilerplate}</p>
        </div>

        <h3>Scores and Feedback:</h3>
        <p style="font-size:105%; font-weight:bold; margin: 15px 0 10px 0;">Overall Score: {$overall_score}</p>
EOF;

        $sections = array(
        	"Excellence of Research" => array(EVL_EXCELLENCE, EVL_EXCELLENCE_COM),
        	"Development of HQP" => array(EVL_HQPDEVELOPMENT, EVL_HQPDEVELOPMENT_COM),
        	"Networking & Partnerships" => array(EVL_NETWORKING, EVL_NETWORKING_COM),
        	"Knowledge & Technology Exchange & Exploitation" => array(EVL_KNOWLEDGE, EVL_KNOWLEDGE_COM),
        	"Management" => array(EVL_MANAGEMENT, EVL_MANAGEMENT_COM),
        	"Quality of Report" => array(EVL_REPORTQUALITY, EVL_REPORTQUALITY_COM),
        	//"Overall Score" => array(EVL_OVERALLSCORE),
        	//"General Comments" => array(EVL_OTHERCOMMENTS)
        );

        $evaluators = $ni->getEvaluators($type, 2012);
        //now loop through all questions and evaluators and get the data
        foreach ($sections as $sec_name => $sec_addr){
        	$html .=<<<EOF
        	<h3>{$sec_name}</h3>
        	<table cellpadding="4" style="page-break-inside: avoid; margin-bottom:15px;" width="100%" align="left;">
EOF;
			$ev_count = 1;

			foreach($evaluators as $eval){
        		$ev_name = $eval->getNameForForms();
        		$ev_id = $eval->getId();
        		
        		$score = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[0], $ni, $ev_id, $curr_year);
        		
        		if(isset($score['revised']) && !empty($score['revised'])){
        			$score = $score['revised'];
        		}else{
        			$score = $score['original'];
        		}

        		
        		$comments = self::getData(BLOB_ARRAY, $rtype,  $sec_addr[1], $ni, $ev_id, $curr_year);
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
        		foreach($comments as $com){
        			$coms[] = substr($com, 2);
        		}
        		$comments = implode("<br />", $coms);
        		$html .=<<<EOF
    	    	<tr>
    	    	<td width="11%"><strong>Reviewer {$ev_count}</strong></td>
    	    	<td width="13%"><i>Score:</i></td>
    	    	<td><i>Comments:</i></td>
        		</tr>
        		<tr>
    	    	<td width="11%">&nbsp;</td>
    	    	<td width="13%">{$score}</td>
    	    	<td>{$comments}</td>
        		</tr>
EOF;
				$ev_count++;
        	}

      		$html .=<<<EOF
        	</table>
        	<br />
EOF;
        }
    	
    	//Overall Score
    	$html .=<<<EOF
        	
EOF;

		//General Comments
		$html .=<<<EOF
			<div style="page-break-inside: avoid;">
        	<h3>General Comments</h3>
        	<table cellpadding="4" width="100%" align="left;" style="page-break-inside: auto;">
EOF;
		$ev_count = 1;
		foreach($evaluators as $eval){
    		$ev_name = $eval->getNameForForms();
    		$ev_id = $eval->getId();

    		$comment = self::getData(BLOB_ARRAY, $rtype,  EVL_OTHERCOMMENTS, $ni, $ev_id, $curr_year);
    		if(isset($comment['revised']) && !empty($comment['revised'])){
    			$comment = $comment['revised'];
    		}else{
    			$comment = $comment['original'];
    		}
    		$comment = nl2br($comment);
        	$html .=<<<EOF
    	    	<tr>
    	    	<td width="11%"><strong>Reviewer {$ev_count}</strong></td>
    	    	<td width="13%"><i>Comments:</i></td>
    	    	<td>{$comment}</td>
        		</tr>
EOF;
			$ev_count++;
        }

        $html .="</table></div>";

        //echo $html;

        $pdf = "";
        try {
            $pdf = PDFGenerator::generate("Report" , $html, "", null, false);
            $filename = $ni->getName();
            $filename .= ".March2013";
            //var_dump($pdf);
            file_put_contents("/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}.pdf", $pdf['pdf']);
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
		
		if($type != "CNI" && $type != "PNI"){
			$type = "PNI";
		}

		$curr_year = REPORTING_YEAR;
		$nis = Person::getAllEvaluates($type); //Person::getAllPeopleDuring($type, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");

		$nis_sorted = array();
		foreach ($nis as $ni){
			$ni_rev_name = $ni->getReversedName();
			$nis_sorted[$ni_rev_name] = $ni;
		}
		ksort($nis_sorted);

		$query = "SELECT * FROM grand_review_results WHERE year={$curr_year} AND type='{$type}'";
		$data = DBFunctions::execSQL($query);

		$fetched = array();
		foreach($data as $row){
            $id = $row['user_id'];
            $fetched[$id] = array('allocated_amount'=>$row['allocated_amount'], 'overall_score'=>$row['overall_score'], 'email_sent'=>$row['email_sent']);    
        }

       

		$html =<<<EOF
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
			<h3>RMC Review Results ({$type})</h3>
			<form id="resultsForm" action='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}' method='post'>
			
			<table width='90%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr>
			<th>NI Name</th>
			<th width="30%">Allocated Amount</th>
			<th width="30%">Overall Score</th>
			<th width="15%">Feedback PDF</th>
			<th width="15%">Email</th>
			</tr>
EOF;
			foreach ($nis_sorted as $ni_name => $ni) {
				$ni_id = $ni->getId();
				$filename = $ni->getName();

				//$ni_name = $ni->getNameForForms();
				$allocated_amount = "";
				$overall_score = "";
				$email_sent = "Email Not Sent";
				$email_sent_bg = "background-color: red;";
				if(isset($fetched[$ni_id])){
					if(isset($fetched[$ni_id]['allocated_amount'])){
						$allocated_amount = $fetched[$ni_id]['allocated_amount'];
					}
					if(isset($fetched[$ni_id]['overall_score'])){
						$overall_score = $fetched[$ni_id]['overall_score'];
					}
					if(isset($fetched[$ni_id]['email_sent']) &&  $fetched[$ni_id]['email_sent'] == 1){
						$email_sent = "Email Sent";
						$email_sent_bg = "background-color: green;";
					}
				}
				if(file_exists("/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}.March2013.pdf")){
					$file_link = "<a href='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}&getPDF={$filename}' target='_blank'>Download</a>"; 
				}else{
					$file_link = "No PDF found";
				}
				$html .=<<<EOF
				<tr>
				<td>{$ni_name}</td>
				<td><input type="text" name="ni[{$ni_id}][allocated_amount]" value="{$allocated_amount}" class="number" /></td>
				<td><input type="text" name="ni[{$ni_id}][overall_score]" value="{$overall_score}" /></td>
				<td align="center">{$file_link}</td>
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
			<input type='submit' name='submit' value='Send out Emails' />
			</form>
EOF;

		$wgOut->addHTML($html);
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
			<h3>RMC Review Results ({$type})</h3>
			<form id="resultsForm" action='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}' method='post'>
			
			<table width='90%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr>
			<th>LOI Name</th>
			<th width="30%">Leader Name</th>
			<th width="30%">Co-Leader Name</th>
			<th width="15%">Feedback PDF</th>
			<th width="15%">Email</th>
			</tr>
EOF;
			foreach ($lois as $loi) {
				$loi_id = $loi->getId();
				$loi_name = $loi->getName();
				$filename = $loi->getName();
				$lead = $loi->getLeadEmail();
				$lead_email = "<a href='mailto:".$lead['email']."'>".$lead['name']."</a>";
				$colead = $loi->getCoLeadEmail();	
				$colead_email = "<a href='mailto:".$colead['email']."'>".$colead['name']."</a>";

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
				// if(file_exists("/local/data/www-root/grand_forum/data/review-feedback/{$type}/{$filename}.March2013.pdf")){
				// 	$file_link = "<a href='$wgServer$wgScriptPath/index.php/Special:ReviewResults?type={$type}&getPDF={$filename}' target='_blank'>Download</a>"; 
				// }else{
				// 	$file_link = "No PDF found";
				// }
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
			
			<input type='submit' name='submit' value='Send out Emails' />
			</form>
EOF;

		$wgOut->addHTML($html);
	}
}

?>
