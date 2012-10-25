<?php

//require_once("Evaluate_Budget.php");
//require_once("Evaluate_Form.php");

//define("EVAL_YEAR", REPORTING_YEAR);

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Evaluate'] = 'Evaluate'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Evaluate'] = $dir . 'Evaluate.i18n.php';
$wgSpecialPageGroups['Evaluate'] = 'reporting-tools';

class Evaluate extends AbstractReportOld {

	function Evaluate(){
	    parent::AbstractReportOld("Evaluate", RMC);
	}
	
	function initReport(){
	    global $reportList, $wgOut, $wgServer, $wgScriptPath, $wgTitle, $reporteeId, $wgUser, $viewOnly;
		$page = "$wgServer$wgScriptPath/index.php/Special:Evaluate";
		
	    $wgOut->addScript("<script type='text/javascript'>
		    function show(id){
		        if($('#sub' + id).css('display') == 'none'){
		            $('#sub' + id).slideDown();
		        }
		        else{
		            $('#sub' + id).slideUp();
		        }
		    }
			function generatePNIReview(eval_name, pni_name, id){
				$.ajax({
				   url: '$page?pniReviewPDF&pni=' + pni_name +'&eval='+eval_name,
				   context: document.body,
				   success: function(data){
					   var data = jQuery.parseJSON(data);
					   $('#blob' + id).attr('href', '$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getReviewPDF=' + data.blob_id);
					   //$('#tst' + id).html(data.time);
					   $('#ni' + id).attr('style', 'display:block');
				   }
				});
			 }
			 function generateProjReview(eval_name, proj_name, id){
				$.ajax({
				   url: '$page?projReviewPDF&proj=' + proj_name +'&eval='+eval_name,
				   context: document.body,
				   success: function(data){
					   var data = jQuery.parseJSON(data);
					   $('#blob' + id).attr('href', '$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getReviewPDF=' + data.blob_id);
					   //$('#tst' + id).html(data.time);
					   $('#ni' + id).attr('style', 'display:block');
				   }
				 });
			 }
		</script>");
	    
	    $viewOnly = false; //True when viewing the report on somebodys behalf, as determined below
        $page = "$wgServer$wgScriptPath/index.php/Special:Report";
        
        $uid = $wgUser->getId();
        $reporteeId = $uid;
		$person = Person::newFromId($uid);
		$autosave = "autosave";
		
		if( $person->isRoleAtLeast(MANAGER) ){
            $onbehalf = ( isset($_GET['person']) )? Person::newFromName($_GET['person']) : NULL;
            $report_type = isset($_GET['evalPDF']);
            
            if( !is_null($onbehalf) ){
                $person = $onbehalf;
                $reporteeId = $onbehalf->getId();
                if(!$report_type){
                    $viewOnly = true;
                    $autosave = "";
                }
            }
        }
	    
	    //if(!defined("REPORT_MIN")){
		    define("REPORT_MIN", 0);
		//}
		$reportList[0] = array( "eval_proj_pdf","All Project PDFs", array("Evaluate", "projPDF"), "" );
	    $reportList[1] = array( "eval_pni_mat","PNI Materials", array("Evaluate", "pniMaterials"), "" );
		$reportList[2] = array( "eval_cni_mat","CNI Materials", array("Evaluate", "cniMaterials"), "" );
	    $reportList[3] = array( "eval_proj_mat","Project Materials", array("Evaluate", "projMaterials"), "" );
        $reportList[4] = array( "eval_pni_budget", "PNI Budget", array("Evaluate", "pniBudget"), "" );
        $reportList[5] = array( "eval_proj_budget", "Project Budget", array("Evaluate", "projBudget"), "" );
        $reportList[6] = array( "pni_review", "PNI Reviews", array("Evaluate", "pniReview"), $autosave );
		$reportList[7] = array( "cni_review", "CNI Reviews", array("Evaluate", "cniReview"), $autosave );
        $reportList[8] = array( "proj_review", "Project Reviews", array("Evaluate", "projReview"), $autosave );
        //$reportList[9] = array( "eval_pdfs", "PDFs", array("Evaluate", "reviewPDFs"), "" );
		//$reportList[8] = array( "eval_proj_pdfs", "Project PDFs", array("Evaluate", "reviewProjPDFs"), "" );
        //if(!defined("REPORT_MAX")){
            define("REPORT_MAX", count($reportList)-1);
        //}
        
        $current_page = OldReport::getCurrentElement();
        $class = "class='{$reportList[$current_page][3]}'";
        $saveAll = "";
        if($reportList[$current_page][3] == "autosave"){
            $saveAll = "saveAll();";
        }
        
        $get_param = "";
		if( $viewOnly ){
		    $get_param = "?person=".$person->getName();
		    $red_message = '* Currently viewing the Evaluation Report from <b>'.$person->getName().'</b>.';
			$class = "";	
		}
		else{
		    $red_message = 'Important: Please do not log-in on this site in multiple browsers to prevent potential data loss due to auto-saving functionality.';
		}
		
		$wgOut->addScript("<script type='text/javascript'>
		                    function submit(i){
		                        $saveAll 
		                        var input = document.createElement('input');
		                        input.type = 'hidden';
		                        input.name = i;
		                        input.value = i;
		                        document.report.appendChild(input);
		                        document.report.submit();
		                     }
				     $(document).ready(function(){
						$('#loadingDiv').hide()  // hide it initially
						$('#contentSub').attr('style', 'margin-left:0px;');
						$('#contentSub').html(
						'<p style=\"font-size:14px; color:red;\">$red_message</p>');
				     });	
				</script>");
        
        $wgOut->addStyle("cavendish/reports.css", "screen");
		$wgOut->addHTML('<div id="loadingDiv" style="position:absolute;"><img width="16" height="16" src="../skins/Throbber.gif" />Please wait while the report is generated.<br />&nbsp;&nbsp;&nbsp;&nbsp;(Should not take longer than a minute)</div>');
        $wgOut->addHTML("<form $class action='$wgServer$wgScriptPath/index.php/Special:{$this->reportName}{$get_param}' method='post' name='report'>");
	}
	
	function run(){
	    global $reportList, $viewOnly, $wgOut;
	    $curr_element = $reportList[OldReport::getCurrentElement()][0];
	    //if( !$viewOnly){
		if($reportList[OldReport::getCurrentElement()][3] == "autosave"){		
	        $wgOut->addHTML("<input id='submit_page' type='hidden' name='' value='' />");
            $wgOut->addHTML("<input class='report_button' type='submit' name='$curr_element' value='Save' /><br />");
        }
        if(isset($_GET['ajaxGetBudget'])){
            ajaxGetBudget();
            exit;
        }
		
		if(isset($_GET['pniReviewPDF'])){
			Evaluate::pniReviewPDF();
			exit;
		}
		
		if(isset($_GET['projReviewPDF'])){
			Evaluate::projReviewPDF();
			exit;
		}
		
		if(isset($_GET['bulkGeneratePDFs'])){
		    Evaluate::bulkGeneratePDFs();
			exit;
		}
		
		if(isset($_GET['getReviewPDF'])){
			Evaluate::triggerPDFDownload();
			exit;
		}
		
	    call_user_func( $reportList[OldReport::getCurrentElement()][2]);
	}
	
	static function projPDF(){
	    global $wgOut, $wgUser, $reporteeId, $wgServer, $wgScriptPath, $wgTitle;
	    
	    $pg = "$wgServer$wgScriptPath/index.php/Special:Evaluate";
		
		// Check for a download.
		$action = CreatePDF::post_string($_GET, 'getpdf');
		if ($action !== "") {
			$p = Person::newFromId($wgUser->getId());
			$sto = new ReportStorage($p);
			$wgOut->disable();
			return $sto->trigger_download($action, "{$action}.pdf", false);
		}
		$wgOut->addHTML("<h2>Project Leader Reports</h2>");
		$wgOut->addHTML("For your reference, here are the project leader reports for <b>all</b> of the projects in GRAND.<br /><br />");
		$wgOut->addHTML("<table>");
		foreach(Project::getAllProjects() as $project){
		    $data = ReportStorage::list_project_reports($project->getId());
		    $wgOut->addHTML("<tr><td align='right'>{$project->getName()}: </td><td>");
	        if(count($data) > 0){
	            $submitor = Person::newFromId($data[0]['user_id']);
	            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getpdf={$data[0]['token']}'>[Download PDF]</a>");
	        }
	        else{
	            $wgOut->addHTML("N/A");
	        }
	        $wgOut->addHTML("</td></tr>");
		}
		$wgOut->addHTML("</table>");
	}
	
	static function pniMaterials() {
		global $wgUser, $wgOut, $reporteeId;
		
		$p = Person::newFromId($reporteeId);
		$repi = new ReportIndex($p);
		
		Evaluate::printPNIDataForm($p);
		
		return true;
	}
	
	static function cniMaterials() {
		global $wgUser, $wgOut, $reporteeId;
		
		$p = Person::newFromId($reporteeId);
		$repi = new ReportIndex($p);
		
		Evaluate::printCNIDataForm($p);
		
		return true;
	}
	
	static function projMaterials() {
		global $wgUser, $wgOut, $reporteeId;
		
		$p = Person::newFromId($reporteeId);
		$repi = new ReportIndex($p);
		
		Evaluate::printProjectDataForm($p);
		
		return true;
	}
	
	static function printPNIDataForm($p){
	    global $wgServer, $wgScriptPath, $wgOut, $wgUser, $wgTitle;
	    $subs = $p->getEvaluateSubs();
	    $repi = new ReportIndex($p);
		$pg = "$wgServer$wgScriptPath/index.php/Special:Evaluate";
		
		// Check for a download.
		$action = CreatePDF::post_string($_GET, 'getpdf');
		if ($action !== "") {
			$p = Person::newFromId($wgUser->getId());
			$sto = new ReportStorage($p);
			$wgOut->disable();
			return $sto->trigger_download($action, "{$action}.pdf", false);
		}
		
		$wgOut->addHTML("<h2>Researchers</h2>");
		$wgOut->addHTML("<table>");
		foreach($subs as $sub){
		    if($sub instanceof Person){
                $wgOut->addHTML("<tr><td>{$sub->getNameForForms()}: </td>");
                $wgOut->addHTML("<td>".Evaluate::getPNIPDF($sub)."</td>");
                $wgOut->addHTML("<td><a target='_blank' href='$wgServer$wgScriptPath/index.php/Special:Report?person={$sub->getName()}'>[View Online Report]</a><br /></td>");
                $wgOut->addHTML("<td><br /></td>");
                $wgOut->addHTML("</tr>");
		    }
		}
		$wgOut->addHTML("</table>");
	}
	
	static function printCNIDataForm($p){
	    global $wgServer, $wgScriptPath, $wgOut, $wgUser, $wgTitle;
	    $subs = $p->getEvaluateCNIs();
	    $repi = new ReportIndex($p);
		$pg = "$wgServer$wgScriptPath/index.php/Special:Evaluate";
		
		// Check for a download.
		$action = CreatePDF::post_string($_GET, 'getpdf');
		if ($action !== "") {
			$p = Person::newFromId($wgUser->getId());
			$sto = new ReportStorage($p);
			$wgOut->disable();
			return $sto->trigger_download($action, "{$action}.pdf", false);
		}
		
		$wgOut->addHTML("<h2>CNI</h2>");
		$wgOut->addHTML("<table>");
		foreach($subs as $sub){
		    if($sub instanceof Person){
                $wgOut->addHTML("<tr><td>{$sub->getNameForForms()}: </td>");
                $wgOut->addHTML("<td>".Evaluate::getPNIPDF($sub)."</td>");
                //$wgOut->addHTML("<td><a target='_blank' href='$wgServer$wgScriptPath/index.php/Special:Report?person={$sub->getName()}'>[View Online Report]</a><br /></td>");
                $wgOut->addHTML("<td><br /></td>");
                $wgOut->addHTML("</tr>");
		    }
		}
		$wgOut->addHTML("</table>");
	}
	
	static function getProjectLeaderPDF($project){
	    global $wgOut, $wgServer, $wgScriptPath, $wgTitle;
	    $data = ReportStorage::list_project_reports($project->getId());
	    if($data != null && count($data) > 0){
	        return "<a href='$wgServer$wgScriptPath/index.php/Special:Evaluate?getpdf={$data[0]['token']}'>[Download&nbsp;PDF]</a>";
	    }
	    else{
	        return "N/A";
	    }
	}
	
	static function getPNIPDF($person){
	    global $wgOut, $wgServer, $wgScriptPath, $wgTitle;
	    $sto = new ReportStorage($person);
        $check = array_merge($sto->list_reports_past($person->getId(), EVAL_YEAR, SUBM, 1, 0 , RPTP_EVALUATOR_NI), 
                             $sto->list_reports_past($person->getId(), EVAL_YEAR, NOTSUBM, 1, 0, RPTP_EVALUATOR_NI)); // Merge submitted and unsubmitted reports
        if (count($check) > 0) {
            $sto->select_report($check[0]['token']);
            $tst = $sto->metadata('timestamp');
            $tok = false;
            $tok = $sto->metadata('token');
        }
        else{
            $tok = false;
            return "N/A";
        }
        return "<a href='$wgServer$wgScriptPath/index.php/Special:Evaluate?getpdf={$tok}'>[Download&nbspPDF]</a>";
	}
	
	static function printProjectDataForm($p){
	    global $wgServer, $wgScriptPath, $wgOut, $wgUser, $wgTitle;
	    $subs = $p->getEvaluateSubs();
	    $repi = new ReportIndex($p);
		$pg = "$wgServer$wgScriptPath/index.php/Special:Evaluate";
		
		// Check for a download.
		$action = CreatePDF::post_string($_GET, 'getpdf');
		if ($action !== "") {
			$p = Person::newFromId($wgUser->getId());
			$sto = new ReportStorage($p);
			$wgOut->disable();
			return $sto->trigger_download($action, "{$action}.pdf", false);
		}

		$wgOut->addHTML("<h2>Projects</h2>");
		$wgOut->addHTML("<table>");
		foreach($subs as $sub){
		    if($sub instanceof Project){
		        $data = ReportStorage::list_project_reports($sub->getId());
		        $wgOut->addHTML("<tr><td align='right'>{$sub->getName()}: </td>");
		        if(count($data) > 0){
		            $submitor = Person::newFromId($data[0]['user_id']);
		            $wgOut->addHTML("<td>".Evaluate::getProjectLeaderPDF($sub)."</td>
		                             <td><a target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ProjectReport?project={$sub->getName()}&person={$submitor->getName()}'>[View Online Report]</a></td>");
		        }else{
		            $leader = $sub->getLeader();
		            $wgOut->addHTML("<td>N/A</td><td><a target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ProjectReport?project={$sub->getName()}&person={$leader->getName()}'>[View Online Report]</a></td>");
		        }
		        $wgOut->addHTML("</tr>");
		    }
		}
		$wgOut->addHTML("</table>");
	}
	
	static function pniBudget(){
	    showEvalBudgets("Researcher");
	}
	
	static function projBudget(){
	    showEvalBudgets("Project");
	}
	
	static function pniReview(){
	    global $reporteeId;
	    $person = Person::newFromId($reporteeId);
	    Evaluate_Form::pniOutput($person);
	}
	
	static function cniReview(){
	    global $reporteeId;
	    $person = Person::newFromId($reporteeId);
	    Evaluate_Form::cniOutput($person);
	}
	
	static function pniReviewPDF(){
	    //global $reporteeId;
	    //$person = Person::newFromId($reporteeId);
	    Evaluate_Form::pniOutputPDF();
	}
	
	static function projReview(){
	    global $reporteeId;
	    $person = Person::newFromId($reporteeId);
	    Evaluate_Form::projOutput($person);
	}	
	
	static function projReviewPDF(){
	  
	    Evaluate_Form::projOutputPDF();
	}
	
	static function reviewPDFs(){
		global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $viewOnly, $reporteeId, $getPerson;
		$getPerson = Person::newFromId($reporteeId);
		$preview = false;
		if($viewOnly == true){
			$preview = true;
		}
		//$wgOut->addHTML("<h3>PNI Review PDFs</h3>");
		//$wgOut->addHTML("<table>");
		$pni_html = "<h3>PNI Review PDFs</h3>";
		$proj_html = "<h3>Project Review PDFs</h3>";
		$noReports = $preview;
		
		$year = EVAL_YEAR;
		$person = Person::newFromId($reporteeId);
		$nis = $person->getEvaluateSubs();
		//print_r ($nis);
        foreach($nis as $ni){
			
			//PNI PDFS	
            if($ni instanceof Person){
				$ni_id = $ni->getId();
				$ni_name = $ni->getName();
				
				$pni_html .= "<h4>{$ni->getNameForForms()}</h4>";
				$pni_html .= "<table>";
				
				$evaluators = $ni->getEvaluators();
				foreach ($evaluators as $ev){
						$ev_id = $ev->getId();
						if($ev_id == "4" || $ev_id == "150"){ //Skip Admin and Adrian
						    continue; 
						}
						$ev_name = $ev->getName();
						$unq_id = "_pni".$ni_id."_".$ev_id;
						
						$pni_html .= "<tr>";
						$pni_html .= "<td>{$ev->getNameForForms()}</td>";
						
						//Init & get the blob
						$blb = new ReportBlob(BLOB_PDF, EVAL_YEAR, $ev_id, 0);
						$addr = ReportBlob::create_address(RP_EVAL_PDF, PDF_PNI, $ni_id, 0);
						$result = $blb->load($addr);
						
						if($result !== false ){
							$blob_id = $blb->getId();
		
							if($preview){
								$pni_html .=<<<EOF
								<td>
								<a id='blob{$unq_id}'  href='$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getReviewPDF={$blob_id}'>Download {$ev->getNameForForms()}'s Review</a>
								</td>
EOF;
								$noReports = false;
							}
							else{
								$pni_html .=<<<EOF
								<td>
								<input type='button' onClick='generatePNIReview("$ev_name", "$ni_name", "$unq_id");' value='Generate PDF' />
								</td>
								<td>
								<span id='ni{$unq_id}'>
								<a id='blob{$unq_id}' href='$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getReviewPDF={$blob_id}'>Download {$ev->getNameForForms()}'s Review</a>
								</span>
								</td>
EOF;
							}
						}
						else if(!$preview){
							$pni_html .=<<<EOF
							<td>
							<input type='button' onClick='generatePNIReview("$ev_name", "$ni_name", "$unq_id");' value='Generate PDF' />
							</td>
							<td>
							<span id='ni{$unq_id}' style='display:none;'><a id='blob{$unq_id}'>Download {$ev->getNameForForms()}'s Review</a>
							</span>
							</td>
EOF;
						}
						$pni_html .= "</tr>";
				}
				$pni_html .= "</table>";
			}
			//PROJECT PDFS
			else if($ni instanceof Project){
				$ni_id = $ni->getId();
				$ni_name = $ni->getName();
				
				$proj_html .= "<h4>{$ni_name}</h4>";
				$proj_html .= "<table>";
				
				$evaluators = $ni->getEvaluators();
				foreach ($evaluators as $ev){
						$ev_id = $ev->getId();
						if($ev_id == "4" || $ev_id == "150"){ //Skip Admin and Adrian
						    continue; 
						}
						$ev_name = $ev->getName();
						$unq_id = "_proj".$ni_id."_".$ev_id;
						
						$proj_html .= "<tr>";
						$proj_html .= "<td>{$ev->getNameForForms()}</td>";
						
						//Init & get the blob
						$blb = new ReportBlob(BLOB_PDF, EVAL_YEAR, $ev_id, 0);
						$addr = ReportBlob::create_address(RP_EVAL_PDF, PDF_PROJ, $ni_id, 0);
						$result = $blb->load($addr);
						
						if($result !== false ){
							$blob_id = $blb->getId();
		
							if($preview){
								$proj_html .=<<<EOF
								<td>
								<a id='blob{$unq_id}'  href='$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getReviewPDF={$blob_id}'>Download {$ev->getNameForForms()}'s Review</a>
								</td>
EOF;
								$noReports = false;
							}
							else{
								$proj_html .=<<<EOF
								<td>
								<input type='button' onClick='generateProjReview("$ev_name", "$ni_name", "$unq_id");' value='Generate PDF' />
								</td>
								<td>
								<span id='ni{$unq_id}'>
								<a id='blob{$unq_id}' href='$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}?getReviewPDF={$blob_id}'>Download {$ev->getNameForForms()}'s Review</a>
								</span>
								</td>
EOF;
							}
						}
						else if(!$preview){
							$proj_html .=<<<EOF
							<td>
							<input type='button' onClick='generateProjReview("$ev_name", "$ni_name", "$unq_id");' value='Generate PDF' />
							</td>
							<td>
							<span id='ni{$unq_id}' style='display:none;'><a id='blob{$unq_id}'>Download {$ev->getNameForForms()}'s Review</a>
							</span>
							</td>
EOF;
						}
						$proj_html .= "</tr>";
				}
				$proj_html .= "</table>";
			}
		}
		
		/*EvaluationTable2012::showEvalTableFor(PNI);
		$wgOut->addHTML($pni_html);
		$wgOut->addHTML("<br /><br /><hr />");
		EvaluationTable2012::showEvalTableFor("Project");
		$wgOut->addHTML($proj_html);
		$wgOut->addHTML("</table>");
		*/
		if($noReports){
			$wgOut->addHTML("<b>No Archived PDFs were found.</b>");
		}
	}
	
	static function bulkGeneratePDFs(){
		 
		$sql = "select eval_id, sub_id, type FROM mw_eval WHERE eval_id NOT IN (4,150) ORDER BY eval_id";
		$data = DBFunctions::execSQL($sql);
	    //$subs = array();
        foreach($data as $row){
			$sub_id = $row['sub_id'];
			$eval_id = $row['eval_id'];
			$eval = Person::newFromId($eval_id);
			$bytes = 0;
			if($row['type'] == "Project"){
				$sub = Project::newFromId($sub_id);
				Evaluate_Form::projOutputPDF($eval, $sub);
				
				//Now, get it from the blob	
				$addr = ReportBlob::create_address(RP_EVAL_PDF, PDF_PROJ, $sub_id, 0);
				$blb = new ReportBlob(BLOB_PDF, EVAL_YEAR, $eval_id, 0);
				$blb->load($addr);
				$pdf = $blb->getData();
				
				$fname = $eval->getName()."_ReviewOf_".$sub->getName().".pdf";
				$handle = fopen("pdf_docs/projects/$fname", "w+");
				if ($handle) {
					$bytes = fwrite($handle,$pdf);
				}
			}
			else if($row['type'] == "Researcher"){
				$sub =  Person::newFromId($sub_id);
				Evaluate_Form::pniOutputPDF($eval, $sub);
				
				//Now, get it from the blob	
				$addr = ReportBlob::create_address(RP_EVAL_PDF, PDF_PNI, $sub_id, 0);
				$blb = new ReportBlob(BLOB_PDF, EVAL_YEAR, $eval_id, 0);
				$blb->load($addr);
				$pdf = $blb->getData();
				
				$fname = $eval->getName()."_ReviewOf_".$sub->getName().".pdf";
				$handle = fopen("pdf_docs/pni/$fname", "w+");	
				
				if ($handle) {
					$bytes = fwrite($handle,$pdf);
				}
			}
			
			echo " $bytes written to $fname<br />";
				
		}
		
		exit;
	}
	
	static function evalSubmit(){
	
	}
	
	static function triggerPDFDownload(){
		global $wgOut;
		
		$blob_id = $_GET['getReviewPDF'];
		$blb = new ReportBlob();
		$result = $blb->loadFromId($blob_id);
		if($result !== false ){
				$pdf = $blb->getData();
				$addr = $blb->getAddress();
				$eval = Person::newFromId($blb->getOwnerId())->getName();
				if($addr['rp_section'] == PDF_PROJ){
						$pni = Project::newFromId($addr['rp_item'])->getName();
				}else{
						$pni = Person::newFromId($addr['rp_item'])->getName();
				}
				$fname = $eval."_ReviewOf_".$pni.".pdf";
				
				$wgOut->disable();
				ob_clean();
				header('Content-Type: application/pdf');
				header('Content-Length: ' . strlen($pdf));
				header('Content-Disposition: attachment; filename="'.$fname.'"');
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
				ini_set('zlib.output_compression','0');
				echo $pdf;
				return true;
		}
		exit;
	}
	
	static function createTab() {
		global $wgServer, $wgScriptPath;
		echo <<<EOM
<li class='top-nav-element'><span class='top-nav-left'>&nbsp;</span>
<a class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:Evaluate' class='new'>Evaluator</a>
<span class='top-nav-right'>&nbsp;</span></li>
EOM;
	}
	
	static function post_field(&$post, $f, $def = false) {
		if (is_array($post) && isset($post[$f])) {
			return $post[$f];
		}
		else {
			return $def;
		}
	}
}

