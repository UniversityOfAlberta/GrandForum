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

	    if(isset($_POST['submit'])){
	    	ReviewResults::handleSubmit();
	    }
	    else if(isset($_GET['generatePDF'])){
	    	//ReviewResults::generateAllFeedback($type);
	    	$ni_id = $_GET['generatePDF'];
		    ReviewResults::generateFeedback($ni_id, $type);
		    exit;
	    }

	    ReviewResults::reviewResults($type);
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

    static function generateAllFeedback($type){
    	if($type != "CNI"){
    		$type = "PNI";
    	}
    	$nis = Person::getAllEvaluates($type); //Person::getAllPeopleDuring($type, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");

    	foreach ($nis as $ni) {
    		$ni_id = $ni->getId();

    		ReviewResults::generateFeedback($ni_id, $type);
    		echo $ni->getNameForForms() ."<br />";
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
			$boilerplate = "GENERIC CNI BOILERPLATE TEXT.";
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
        	<table cellpadding="4" width="100%" align="left;">
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
            //var_dump($pdf);
            file_put_contents("/local/data/www-root/grand_forum/data/review-feedback/{$filename}.pdf", $pdf['pdf']);
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
            $fetched[$id] = array('allocated_amount'=>$row['allocated_amount'], 'overall_score'=>$row['overall_score']);    
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
			
			<table width='70%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr>
			<th>NI Name</th>
			<th width="30%">Allocated Amount</th>
			<th width="30%">Overall Score</th>
			</tr>
EOF;
			foreach ($nis_sorted as $ni_name => $ni) {
				$ni_id = $ni->getId();
				//$ni_name = $ni->getNameForForms();
				$allocated_amount = "";
				$overall_score = "";
				if(isset($fetched[$ni_id])){
					if(isset($fetched[$ni_id]['allocated_amount'])){
						$allocated_amount = $fetched[$ni_id]['allocated_amount'];
					}
					if(isset($fetched[$ni_id]['overall_score'])){
						$overall_score = $fetched[$ni_id]['overall_score'];
					}
				}
				$html .=<<<EOF
				<tr>
				<td>{$ni_name}</td>
				<td><input type="text" name="ni[{$ni_id}][allocated_amount]" value="{$allocated_amount}" class="number" /></td>
				<td><input type="text" name="ni[{$ni_id}][overall_score]" value="{$overall_score}" /></td>
				</tr>
EOF;

			}

			$html .=<<<EOF
			</table>
			<br />
			<input type='hidden' name='ni_type' value='{$type}' />
			<input type='hidden' name='year' value='{$curr_year}' />
			<input type='submit' name='submit' value='Submit' />
			</form>
EOF;

		$wgOut->addHTML($html);
	}
}

?>
