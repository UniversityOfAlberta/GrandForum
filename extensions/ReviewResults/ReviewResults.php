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
	    if(isset($_POST['submit'])){
	    	ReviewResults::handleSubmit();
	    }
	    ReviewResults::reviewResults('PNI');
	    //$wgOut->addHTML();
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
				$allocated_amount = (!empty($ni_data['allocated_amount']))? $ni_data['allocated_amount'] : 0;
				$overall_score = (!empty($ni_data['overall_score']))? $ni_data['overall_score'] : 0;
				if(empty($ni_data['allocated_amount']) && empty($ni_data['overall_score'])){
					continue;
				}

				$query =<<<EOF
				INSERT INTO grand_review_results (user_id, type, year, allocated_amount, overall_score)
				VALUES ({$ni_id}, '{$type}', {$year}, {$allocated_amount}, {$overall_score})
				ON DUPLICATE KEY UPDATE
				allocated_amount = {$allocated_amount},
				overall_score = {$overall_score}
EOF;
				$result = DBFunctions::execSQL($query, true);
				

			}
		}
		else{
			$result = false;
		}
		
		if($result){
			$wgMessage->addSuccess("Review Results updated successfully!");
		}
		else{
			$wgMessage->addError("There was a problem with saving Review Results. Please contact support if the problem persists.");
		}
	}




	static function reviewResults($type){
		global $wgOut, $wgScriptPath, $wgServer, $wgUser;

		$my_id = $wgUser->getId();
		$me = Person::newFromId($wgUser->getId());
		
		if($type != "CNI" && $type != "PNI"){
			$type = "PNI";
		}

		$curr_year = REPORTING_YEAR;
		$nis = Person::getAllPeopleDuring($type, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");

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
			<form id="resultsForm" action='$wgServer$wgScriptPath/index.php/Special:ReviewResults' method='post'>
			
			<table width='70%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr>
			<th>NI Name</th>
			<th width="30%">Allocated Amount</th>
			<th width="30%">Overall Score</th>
			</tr>
EOF;
			foreach ($nis as $ni) {
				$ni_id = $ni->getId();
				$ni_name = $ni->getNameForForms();
				$allocated_amount = "";
				$overall_score = "";
				if(isset($fetched[$ni_id])){
					if(!empty($fetched[$ni_id]['allocated_amount'])){
						$allocated_amount = $fetched[$ni_id]['allocated_amount'];
					}
					if(!empty($fetched[$ni_id]['overall_score'])){
						$overall_score = $fetched[$ni_id]['overall_score'];
					}
				}
				$html .=<<<EOF
				<tr>
				<td>{$ni_name}</td>
				<td><input type="text" name="ni[{$ni_id}][allocated_amount]" value="{$allocated_amount}" class="number" /></td>
				<td><input type="text" name="ni[{$ni_id}][overall_score]" value="{$overall_score}" class="number" /></td>
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
