<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ProjectReport'] = 'ProjectReport'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ProjectReport'] = $dir . 'ProjectReport.i18n.php';
$wgSpecialPageGroups['ProjectReport'] = 'reporting-tools';

class ProjectReport extends AbstractReportOld{

	function ProjectReport() {
	    parent::AbstractReportOld("ProjectReport", "Leadership");
	}
	
	function initReport(){
        global $reportList, $wgUser, $wgOut, $wgServer, $wgScriptPath, $project, $viewOnly, $reporteeId;
        //$viewOnly = true; //True when viewing the report on somebodys behalf, as determined below
        $noAccess = false; //Usually is set to true if CNI/PNI, but not a (co)leader of project
        
        $uid = $wgUser->getId();
        $reporteeId = $uid;
        $reporteeName = "";
		$person = Person::newFromId($uid);
		$autosave = "autosave";
        
		if(isset($_GET['project'])){
		    $project = Project::newFromName( $_GET['project'] );
		}
		else if(isset($_POST['project'])){
		    $project = Project::newFromName( $_POST['project'] );
		}
		
		$project_name = $project->getName();
		
		if( $person->isRoleAtLeast(MANAGER) && !$person->leadershipOf($project->getName()) ){
		    $onbehalf = ( isset($_GET['person']) )? Person::newFromName($_GET['person']) : NULL;
		    if(is_null($onbehalf)){
		        $onbehalf = ( isset($_POST['person']) )? Person::newFromName($_POST['person']) : NULL;
		    }
            if( !is_null($onbehalf) ){
                $person = $onbehalf;
                $reporteeId = $onbehalf->getId();
                $reporteeName = $onbehalf->getName();
                $viewOnly = true;
                $autosave = "";                
            }else{
                $noAccess = true;
            }
        }
        else if($person->isRole(RMC)){
            $onbehalf = ( isset($_GET['person']) )? Person::newFromName($_GET['person']) : NULL;
            if(!is_null($onbehalf)){
                foreach($person->getEvaluateSubs() as $sub){
                    if($sub instanceof Project){
                        if($sub->getName() == $project->getName()){
                            $person = $onbehalf;
                            $reporteeId = $onbehalf->getId();
                            $reporteeName = $onbehalf->getName();
                            $viewOnly = true;
                            $autosave = "";
                            break;
                        }
                    }
                }
            }
            if($viewOnly == false){
                $noAccess = true;
            }
        }
        if($person->leadershipOf($project->getName())){
            $noAccess = false;
            $viewOnly = false;
        }
		
		$viewOnly = ( $viewOnly || (FROZEN));
		if($viewOnly){
		    $autosave = "";
		}
		
		$person_name = $person->getName();
		$page = "$wgServer$wgScriptPath/index.php/Special:ProjectReport?project=$project_name&person=$person_name";
		
		define("REPORT_MIN", 0);
		
        if( !$noAccess && ($person->isCNI() || $person->isPNI() || $person->isRoleAtLeast(MANAGER)) ){
            
            $reportList[0] = array( "dashboard","Project Dashboard", array("ProjectReport","ProjectDashboard"), "" );
            $reportList[1] = array( "questionnaire", "Project Questionnaire", array("ProjectReport","ProjectQuestionnaire"), "");
            $reportList[2] = array( "report", "Project Report", array("ProjectReport","ProjectReportTab"), $autosave );
            $reportList[3] = array( "nicomments", "Comments", array("ProjectReport","NICommentsTab"), $autosave );
            $reportList[4] = array( "budget", "Budget", array("ProjectReport","BudgetTab"), $autosave );
            $reportList[5] = array( "prsubmit", "Review & Submit", array("ProjectReport", "SubmitReport"), "" );
        }
        else{
            $reportList[0] = array( "noaccess","Permission Denied", array("ProjectReport","PermissionDenied"), "" );
        }
        
        define("REPORT_MAX", count($reportList)-1);
        
        $current_page = OldReport::getCurrentElement();
        $class = "class='{$reportList[$current_page][3]}'";
        $saveAll = "";
        if($reportList[$current_page][3] == "autosave"){
            $saveAll = "saveAll();";
        }
        
        $wgOut->addScript("<script src='../scripts/jquery.limit-1.2.source.js' type='text/javascript' charset='utf-8';></script>");
        $get_param = "?project={$project->getName()}";
		if( $viewOnly ){
		    $get_param = "?project={$project->getName()}&person=".$person->getName();
		    
		    $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        $('#contentSub').attr('style', 'margin-left:0px;');
                                        $('#contentSub').html(
                                        '<p style=\"color:red;font-style:italic;font-size:12px; \">* Currently viewing a report that you cannot edit</p>');
                                    });
                                </script>");
		}
		else{
		    $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        $('#contentSub').attr('style', 'margin-left:0px;');
                                        $('#contentSub').html(
                                        '<a style=\"display:inline-block; font-size: 12px; font-weight: bold; margin-top: 7px;\" href=\"/index.php/GRAND:Reporting_2011_Instructions\" target=\"_blank\">2011 Reporting-Process Overview</a><p style=\"font-size:14px; color:red;\">Important: Please do not log-in on this site in multiple browsers to prevent potential data loss due to auto-saving functionality. For co-PLs who wish to work together you should consider skype screen sharing with one editor sharing his/her screen with the the second partner, who is acting as a viewer. </p>');
                                    });
                                </script>");
		}        
    
        $wgOut->addScript("<script type='text/javascript'>
		                        function submit(i){
    		                        $saveAll
    		                        var input = document.createElement('input');
    		                        input.type = 'hidden';
    		                        input.name = i;
    		                        input.value = i;
    		                        document.report.appendChild(input);
    		                        $('#prsubmit').remove();
                                    $('#action_type').remove();
    		                        document.report.submit();
    		                    }

                                function submitReportAction(action){
    		                         var input = document.createElement('input');
     		                         input.type = 'hidden';
     		                         input.id = 'prsubmit';
     		                         input.name = 'prsubmit';
     		                         input.value = 'prsubmit';
     		                         document.report.appendChild(input);

     		                         $('#action_type').val(action);
     		                         document.report.submit();
    		                     }
    		                     function generateReport(){
    		                         $('#loadingDiv').show();
     		                         $.ajax({
                                        url: '$page&generatePDF',
                                        context: document.body,
                                        success: function(data){
                                            $('#loadingDiv').hide();
                                            var data = jQuery.parseJSON(data);
                                            $('#ex_token').html(data.tok);
                                            $('#ex_time').html(data.time);
                                            $('#ex_token2').val(data.tok);
                                            $('#markrptok').val(data.tok);
                                            $('#no_download_button').hide();
                                            $('#download_button').show();
                                            $('#report_submit_div').show();
                                            $('#submit_status_cell').attr('style', 'background-color:red;');
                                            $('#submit_status_cell').html('<b>No</b>');

                                            //alert(data.tok);
                                            //window.location.href=window.location.href;//location.reload();
                                        }
                                      });
                                      $.ajax({
                                          url: '$page&generateCommentsPDF',
                                          context: document.body,
                                          success: function(data){
                                              var data = jQuery.parseJSON(data);
                                              $('#ex_token_com').html(data.tok);
                                              $('#ex_time_com').html(data.time);
                                              $('#ex_token2_com').val(data.tok);
                                              $('#markrptok_com').val(data.tok);
                                              $('#no_download_button_com').hide();
                                              $('#download_button_com').show();
                                              //$('#report_submit_div').show();
                                             // $('#submit_status_cell').attr('style', 'background-color:red;');
                                              //$('#submit_status_cell').html('<b>No</b>');

                                              //alert(data.tok);
                                              //window.location.href=window.location.href;//location.reload();
                                          }
                                        });
     		                     }
				            </script>");
		$wgOut->addStyle("cavendish/reports.css", "screen");
        
        $wgOut->addHTML("<form $class action='$wgServer$wgScriptPath/index.php/Special:{$this->reportName}{$get_param}' method='post' name='report' enctype='multipart/form-data'><input type='hidden' name='project' value='$project_name' /><input type='hidden' name='person' value='$reporteeName' />");
        
    }
	
	function run(){
		global $wgUser, $wgOut, $wgServer, $wgScriptPath, $project, $reportList, $viewOnly;
		
		$instructions_html = '<div id="loadingDiv" style="position:absolute;display:none;"><img width="16" height="16" src="../skins/Throbber.gif" />Please wait while the report is generated.</div><div id="instructions"><img style="display:none;" width="16" height="16" src=""../skins/Throbber.gif" />';
		$curr_element = $reportList[OldReport::getCurrentElement()][0];
		if( OldReport::getCurrentElement() >= 0 && OldReport::getCurrentElement() < 5 ){
		    $instructions_path = "$wgServer$wgScriptPath/extensions/Report/Instructions/project_".$curr_element.".html";
		    $instructions_html .=<<<EOF
		    <input class="report_button" type="button" name="instr" value="Instructions" 
		    onClick='window.open("$instructions_path", "Report Instructions", "width=800,height=600,scrollbars=yes");' /> 
EOF;
        }
        
        if( !$viewOnly && OldReport::getCurrentElement() > 0 && OldReport::getCurrentElement() != 5 ){
            $instructions_html .=<<<EOF
		    <input class="report_button" type="submit" name="$curr_element" value="Save" /><br />
EOF;
        }
		$wgOut->addHTML($instructions_html."</div>");
		
		call_user_func( $reportList[OldReport::getCurrentElement()][2] );
	}
	
	function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgUser;
        $generatePDF = (isset($_GET['generatePDF']));
        $generateCommentsPDF = (isset($_GET['generateCommentsPDF']));
        $generateOverviewsPDF = (isset($_GET['generateOverviewsPDF']));
        
        if(!$generatePDF && !$generateCommentsPDF){
            $this->setHeaders();
    		if ( $this->userCanExecute( $wgUser ) ) {
    			$this->outputHeader();
    		} else {
    			$this->displayRestrictionError();
    			return;
    		}
        }
				    
        if(!$this->initialized){
            $this->initReport();
            $this->initialized = true;
        }
        if(!$generatePDF && !$generateCommentsPDF){
            OldReport::printPreviousNext();
        }
        
        $this->run();
        
        if($generatePDF && $par != "noPDF"){
            $this->generatePDF();
        }
        if($generateCommentsPDF && $par != "noPDF"){
            $this->generateCommentsPDF();
        }
        
        if(!$generatePDF && !$generateCommentsPDF){
            OldReport::printPreviousNext();
        }
        if(!$generatePDF && !$generateCommentsPDF){
            $wgOut->addHTML("</form>");
        }
        if($generateOverviewsPDF && $par != "noPDF"){
            $this->generateOverviewsPDF();
        }
    }
	
	// Generates the Full PDF of this report, using dompdf.  
    // Loops through all the sections of the report, and puts together a string of html
    // to be used as input for dompdf
    function generatePDF(){
        global $reportList, $wgOut, $project, $reporteeId;
        ini_set('max_execution_time', 300);
        ini_set("memory_limit","1024M");
        $project_name = $project->getName();
        $head = "";
        $wgOut->clearHTML();
        $report_order = array(0,2,4,1);
        //for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
        foreach($report_order as $i){
            $page = $reportList[$i][0];
            
            $_POST[$page] = true;
            if($i == 1){
                $heading_text = "Appendix B (Milestones)";
                $heading_id = "id='appendix_b'";
            }
            else{
                $heading_text = $reportList[$i][1];
                $heading_id = "";
            }
            $wgOut->addHTML("<center $heading_id><h1>{$heading_text}</h1></center>");
            $this->execute("noPDF");
            unset($_POST[$page]);
            $wgOut->addHTML("<div style='page-break-after:always;'></div>");
        }
        
        /*
         * INITIAL CLEANUP
         */
        $html = str_get_html($wgOut->getHTML(), true, true, DEFAULT_TARGET_CHARSET, false); // Create the dom object
        $dashboards = $html->find('table.dashboard');
        $i = 0;
        foreach($dashboards as $dashboard){
            if(count($dashboards) == $i+1){
                $dashboard->style = str_replace("page-break-after:always;", "", $dashboard->style);
            }
            $i++;
        }
        foreach($html->find('a') as $a){
            $a->tag = 'b';  // Convert all links to bold text
        }
        //$html->find('table[id=dashboard] thead', 0)->outertext = "";   // Get rid of the head table entries for the dashboard
        foreach($html->find('input[type=button]') as $button){
            $button->outertext = "";
        }
        foreach($html->find('button') as $button){
            $button->outertext = "";
        }
        foreach($html->find('select') as $select){
            $select->tag = 'span';
            $select->style = 'display:none;';
        }
        foreach($html->find("#instructions") as $inst){
            $inst->outertext = '';
        }
        //Remove all 'Save' buttons
        foreach( $html->find("input[type=submit]") as $submit ){
            $submit->outertext = "";
        } 
        //cleanup hrefs leftover from converting a to b
        foreach( $html->find("b") as $b){
            $b->href = null;
            $b->onclick = null;
            $b->target = null;
        }
        
        foreach( $html->find("textarea") as $textarea ){
            $textarea->rows = null;
            $textarea->style = null;
            $textarea->outertext = nl2br($textarea->plaintext);
        }
        $html2 = str_get_html($html->save());
        
        foreach( $html->find("#loadingDiv") as $loading ){
            $loading->outertext = "";
        }
        
        //HIDE and SHOW sections accordingly
        foreach ($html->find("[class=pdf_hide]") as $el){
            $el->outertext = "";
        }
        foreach ($html->find("[class=pdf_show]") as $el){
            $el->style = "display:block;";
        }
        //$html2 = str_get_html($html->save());
        
        // Budget Manipulations
        $html->find("#ldr_budget_wrapper b[id=Budget]", 0)->outertext = "";
        $html->find("#ldr_budget_wrapper h3", 0)->outertext = "";
        $html->find("#ldr_budget_wrapper h2", 0)->outertext = "";
        $html->find("#ldr_budget_wrapper ul", 0)->outertext = "";
        
        //Get a different budget table rotated 90deg
        $budget_just = $html->find('[id=div_budget_just]', 0);
        $budget_just2 = $html2->find('[id=div_budget_just]', 0);
        if($budget_just != null){
            $budget_just->outertext = '';
            $budget_just2->class = '';
            $budget_just2->find('p', 0)->outertext = '';
        }
        
        $budget_legend = array(
            "Name of network investigator submitting request:" => "Name of NI",
            "1) Salaries and stipends" => "",
            "a) Graduate students" => "1a)",
            "b) Postdoctoral fellows" => "1b)",
            "c) Technical and professional assistants" => "1c)",
            "d) Undergraduate students" => "1d)",
            "2) Equipment" => "",
            "a) Purchase or rental" => "2a)",
            "b) Maintenance costs" => "2b)",
            "c) Operating costs" => "2c)",
            "3) Materials and supplies" => "3)",
            "4) Computing costs" => "4)",
            "5) Travel expenses" => "",
            "a) Field trips" => "5a)",
            "b) Conferences" => "5b)",
            "c) GRAND annual conference" => "5c)"
        );
        
        $budget_legend_html = "<h3>Table Legend:</h3><div>";
        foreach ($budget_legend as $i => $j){
            if($i == "Name of network investigator submitting request:"){
                continue;
            }
            if($i == "Budget Categories for April 1, 2012, to March 31, 2013"){
                $i = "* Budget Categories for April 1, 2012, to March 31, 2013";
            }
            if($i == "1) Salaries and stipends" ){
                $budget_legend_html .= "<div>$i<div style='padding-left:14px;'>";
            }
            else if( $i == "d) Undergraduate students" ){
                $budget_legend_html .= "<div>$i</div></div></div>";
            }
            else if($i == "2) Equipment" ){
                $budget_legend_html .= "<div>$i<div style='padding-left:14px;'>";
            }
            else if( $i == "c) Operating costs" ){
                $budget_legend_html .= "<div>$i</div></div></div>";
            } 
            else if($i == "5) Travel expenses" ){
                $budget_legend_html .= "<div>$i<div style='padding-left:14px;'>";
            }
            else if( $i == "c) GRAND annual conference" ){
                $budget_legend_html .= "<div>$i</div></div></div>";
            }   
            else{
                $budget_legend_html .= "<div>$i</div>";
            }
        }
        $budget_legend_html .= "</div>";
        
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $budget_html = $budget->copy()->rasterize()
                              ->filter(HEAD1, array("Budget Categories for April 1, 2012, to March 31, 2013", 
                                                    "1) Salaries and stipends",
                                                    "2) Equipment",
                                                    "5) Travel expenses"))
                              ->transpose()
                              ->render();
        
        $new_budget = str_get_html($budget_html);
        
        $new_budget_tbl = $new_budget->find("table[id=budget]", 0);
        if($new_budget_tbl != null){ 
             
            $new_budget_tbl->cellpadding = '1';
            $new_budget_tbl->cellspacing = '1';
            $new_budget_tbl->rules = null;
            $new_budget_tbl->boxes = null;
            $new_budget_tbl->frame = null;
            $new_budget_tbl->style = 'background-color:#000000;margin-bottom:15px;';
            $new_budget_tbl->width = '100%';
            foreach($new_budget_tbl->find('td') as $td){
                $td->nowrap = null;
                $td->style = 'background-color:#FFFFFF; text-align:right;';
                $td->colspan = null;
                //if($td->find('b', 0) != null){
                //    $td->innertext = "<small>".$td->plaintext."</small>";
                //}
            }
            foreach($new_budget_tbl->find('tr') as $tr){
                $tr->find("td", 0)->style = 'background-color:#FFFFFF; text-align:left;';
            }    
            foreach ($new_budget_tbl->find('tr', 0)->find("td b") as $hdr){
                //$hdr->innertext = implode('<br />', str_split($hdr->innertext, 4)); 
                $hdr->innertext = (isset($budget_legend[$hdr->innertext]))? $budget_legend[$hdr->innertext] : $hdr->innertext;
            }
            
            $new_budget_tbl->outertext = $new_budget_tbl->outertext.$budget_just2->innertext.$budget_legend_html;
        }
        else if($budget_just != null){
            $budget_just->outertext = $budget_just2->outertext;
        }
        
        $html->find('table[id=budget]', 0)->outertext = $new_budget_tbl->outertext; 
        
        /* Comments */
        //$html->find('#ldr_comments_wrapper', 0)->find("h2", 0)->outertext = "";
        
        /* Questionnaire */
        
        foreach( $html->find(".project_ldr_q_wrapper legend") as $l ){
            $value = $l->innertext;
            $l->outertext = "<h3>$value</h3>";
        }
        
        //this is last step to convert fieldsets to divs
        foreach( $html->find(".project_ldr_q_wrapper fieldset") as $f ){
            $inner = $f->innertext;
            $f->outertext = "<div style='margin: 20px 0;'>$inner</div>";
        }
        
        foreach( $html->find("#ldr_report_wrapper h2") as $h2 ){
            $h2->tag = "h3";
            $h2->style = "font-size:15px";
        }
        
        $details = "";
        foreach($html->find('.pdfDetailsDiv') as $div){
            $details .= $div->outertext;
            $div->outertext = "";
        }
        
        $html->find("#appendix_b", 0)->outertext = "<center><h1>Appendix A (Details: Partners, HQP, Publications, Artifacts, Activities, Awards, Contributions)</h1></center>$details<div style='page-break-after:always;'></div>".$html->find("#appendix_b", 0)->outertext;        
        //Generate
        $newpdf = "";
        try {
            $dompdf = PDFGenerator::generate("Report" , $html, $head, false);
            //exit;
        }
        catch(DOMPDF_Internal_Exception $e){
            echo "ERROR!!!";
            echo $e->getMessage();
            // TODO: Display a nice message to the user if the generation failed
        }
        //$pdfdata = $dompdf->stream($project_name."_ProjectReport.pdf");
        
        $newpdf = $dompdf->output();
        $data = "";
        $person = Person::newFromId($reporteeId);
        $sto = new ReportStorage($person);
        $ind = new ReportIndex($person);
        $sto->store_report($data, $newpdf, 0, 0, 2); // Project leader report type = 2
		$rid = $sto->metadata('report_id');
		$tok = $sto->metadata('token');
        $tst = $sto->metadata('timestamp');
        $len = $sto->metadata('pdf_len');
        $ind->insert_report($rid, $project);
         
        echo json_encode(array('tok'=>$tok, 'time'=>$tst, 'len'=>$len));
        exit;
    }
    
    
    // Generates the Full PDF of this report, using dompdf.  
    // Loops through all the sections of the report, and puts together a string of html
    // to be used as input for dompdf
    function generateCommentsPDF(){
        global $reportList, $wgOut, $project, $reporteeId;
        $project_name = $project->getName();
        $head = "";
        $wgOut->clearHTML();
        $report_order = array(3);
        
        foreach($report_order as $i){
            $page = $reportList[$i][0];
            
            $_POST[$page] = true;
            
            $heading_text = $reportList[$i][1];
            $heading_id = "";
            
            $wgOut->addHTML("<center $heading_id><h1>{$heading_text}</h1></center>");
            $this->execute("noPDF");
            unset($_POST[$page]);
            $wgOut->addHTML("<div style='page-break-after:always;'></div>");
        }
        
        /*
         * INITIAL CLEANUP
         */
          
        $html = str_get_html($wgOut->getHTML(), true, true, DEFAULT_TARGET_CHARSET, false); // Create the dom object
        foreach($html->find('a') as $a){
            $a->tag = 'b';  // Convert all links to bold text
        }
        /*
        foreach($html->find('input[type=button]') as $button){
            $button->outertext = "";
        }
        foreach($html->find('button') as $button){
            $button->outertext = "";
        }
        */
        foreach($html->find("#instructions") as $inst){
            $inst->outertext = '';
        }
        
        //Remove all 'Save' buttons
        foreach( $html->find("input[type=submit]") as $submit ){
            $submit->outertext = "";
        } 
        //cleanup hrefs leftover from converting a to b
        foreach( $html->find("b") as $b){
            $b->href = null;
            $b->onclick = null;
        }
        
        foreach( $html->find("textarea") as $textarea ){
            $textarea->rows = null;
            $textarea->style = null;
            $textarea->outertext = nl2br($textarea->plaintext);
        }
       
        foreach( $html->find("#loadingDiv") as $loading ){
            $loading->outertext = "";
        }
        
        //HIDE and SHOW sections accordingly
        foreach ($html->find("[class=pdf_hide]") as $el){
            $el->outertext = "";
        }
        foreach ($html->find("[class=pdf_show]") as $el){
            $el->style = "display:block;";
        }
        
        $html->find("h1", 0)->outertext = "<div style='font-size:30px; font-weight:bold; padding-bottom: 30px;'>$project_name </div>". $html->find("h1", 0)->outertext;
        
        /* Comments */
        $html->find('#ldr_comments_wrapper', 0)->find("h2", 0)->outertext = "";
    
        //Generate
        $newpdf = "";
        try {
            $dompdf = PDFGenerator::generate("Report" , $html, $head, false);
            //exit;
        }
        catch(DOMPDF_Internal_Exception $e){
            echo "ERROR!!!";
            echo $e->getMessage();
            // TODO: Display a nice message to the user if the generation failed
        }
        //$pdfdata = $dompdf->stream($project_name."_ProjectReport_comments.pdf");
        
        
        $newpdf = $dompdf->output();
        $data = "";
        $person = Person::newFromId($reporteeId);
        $sto = new ReportStorage($person);
        $ind = new ReportIndex($person);
        $sto->store_report($data, $newpdf, 0, 0, RPTP_LEADER_COMMENTS); // Project leader comments report type = 7(?)
        $rid = $sto->metadata('report_id');
		$tok = $sto->metadata('token');
        $tst = $sto->metadata('timestamp');
        $len = $sto->metadata('pdf_len');
        $ind->insert_report($rid, $project);
        
        echo json_encode(array('tok'=>$tok, 'time'=>$tst, 'len'=>$len));
        exit;
    }    

    // Generates the Full PDF of this report, using dompdf.  
    // Loops through all the sections of the report, and puts together a string of html
    // to be used as input for dompdf
    function generateOverviewsPDF(){
        global $reportList, $wgOut, $project, $reporteeId;
       
        $wgOut->clearHTML();
        
        $html = "<h1>Project Overviews</h1>";
        $projects = Project::getAllProjects();
        
        $year = REPORTING_YEAR;
        $blob_type = BLOB_TEXT;
        $rptype = RP_LEADER;
        
        $rep_addr = ReportBlob::create_address($rptype, LDR_RESACTIVITY, LDR_RESACT_OVERALL, 0);
        
        foreach ($projects as $p){
            
            $p_id = $p->getId();
            $p_name = $p->getName();
            $overall_activity_blb = new ReportBlob($blob_type, $year, 0, $p_id);
            $overall_activity_blb->load($rep_addr);
            $overall_activity = nl2br($overall_activity_blb->getData());
            
            $html .= "<h3>$p_name</h3>";
            $html .= "<p>$overall_activity</p>";
            
        }
    
        //Generate
        $newpdf = "";
        try {
            $dompdf = PDFGenerator::generate("Report" , $html, "", false);
            //exit;
        }
        catch(DOMPDF_Internal_Exception $e){
            echo "ERROR!!!";
            echo $e->getMessage();
            // TODO: Display a nice message to the user if the generation failed
        }
        
        
        
        //$newpdf = $dompdf->output();
        $data = "";
        $pdfdata = $dompdf->stream("ProjectOverviews.pdf");
        //$person = Person::newFromId($reporteeId);
        //$sto = new ReportStorage($person);
       // $ind = new ReportIndex($person);
        //$sto->store_report($data, $newpdf, 0, 0, RPTP_LEADER_COMMENTS); // Project leader comments report type = 7(?)
        //$rid = $sto->metadata('report_id');
        //$tok = $sto->metadata('token');
        //$tst = $sto->metadata('timestamp');
        //$len = $sto->metadata('pdf_len');
        //$ind->insert_report($rid, $project);
        
        //echo json_encode(array('tok'=>$tok, 'time'=>$tst, 'len'=>$len));
        exit;
    }    
    
    private function ProjectDashboard(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $project;
        
        $year = REPORTING_YEAR;
        $project_id = $project->getId();
        $project_name = $project->getName();
        
	    $wgOut->setPageTitle("Project Dashboard: $project_name");    
        
       
        //Other Project PNIs & CNIs
	    
	    //Time and Budget Section
        $wgOut->addHTML("<h3>Time and Budget:</h3>");
        $dashboard = new DashboardTable(PROJECT_REPORT_TIME_STRUCTURE, $project);
        $top = $dashboard->copy()->limit(0, 1);
        if(isset($_GET['generatePDF'])){
            for($i = 1; $i < $dashboard->nRows(); $i+=19){
                $wgOut->addHTML($top->copy()->union($dashboard->copy()->limit($i, 10))->renderForPDF());
            }
	    }
	    else{
	        $wgOut->addHTML($dashboard->render());
	    }

        //Productivity Section
        $wgOut->addHTML("<h3>Productivity:</h3>");
        $dashboard = new DashboardTable(PROJECT_REPORT_PRODUCTIVITY_STRUCTURE, $project);
        $top = $dashboard->copy()->limit(0, 1);
	    if(isset($_GET['generatePDF'])){
	        for($i = 1; $i < $dashboard->nRows(); $i+=10){
                $wgOut->addHTML($top->copy()->union($dashboard->copy()->limit($i, 5))->renderForPDF());
            }
	    }
	    else{
	        $wgOut->addHTML($dashboard->render());
	    }
	}
	
	//Project Questionnaire
    static function ProjectQuestionnaire(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $project, $viewOnly;
        $months = array(0 => "--",
                        1 => "January",
                        2 => "February",
                        3 => "March",
                        4 => "April",
                        5 => "May",
                        6 => "June",
                        7 => "July",
                        8 => "August",
                        9 => "September",
                        10 => "October",
                        11 => "November",
                        12 => "December");
                        
	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    //$uid = $reporteeId; //$wgUser->getId();
	    
	    //Address for getting NI comments on a milestone
		$blob_type = BLOB_ARRAY;
		$rptype = RP_RESEARCHER;
    	$section = RES_MILESTONES;
    	$item = RES_MIL_CONTRIBUTIONS;
    	$subitem = 0;
		$rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);

        //Render the page
        $project_name = $project->getName();
        $project_id = $project->getId();
        
	    $wgOut->setPageTitle("Project Questionnaire: $project_name");
	    $pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    //$person = Person::newFromId($uid);
        
        $custom_js =<<<EOF
            <script type='text/javascript'>
            function clearCheck(id){
                $('#' + id).attr('checked',false);
            }
            
            function showComment(id){
                var checked = $('#' + id).attr('checked');
                if(checked){
                    id = id.replace(/_abandoned/g, '_comment').replace(/_closed/g, '_comment');
                    $('#' + id).css('display', 'table-row');
                }
                else{
                    id = id.replace(/_abandoned/g, '_comment').replace(/_closed/g, '_comment');
                    $('#' + id).css('display', 'none');
                }
            }
            
            var mI = 0;
            </script>
EOF;
        
	    $custom_js .=<<<EOF
	        <script type='text/javascript'>    
    		$(document).ready(function () {	    
EOF;


        $wgOut->addHTML("<div class='project_ldr_q_wrapper'>");
        
        //Lets get all NI comment blobs for this project. We will sort them out by milestones later
        
        $pni_objs = $project->getAllPeopleDuring(PNI);
        $cni_objs = $project->getAllPeopleDuring(CNI);
        $ni_objs = array_merge($pni_objs, $cni_objs);
        $milestone_ni_comments = array();
        $alreadySeen = array();
        foreach ($ni_objs as $ni){
            $ni_id = $ni->getId();
            if(!isset($alreadySeen[$ni_id])){
                $alreadySeen[$ni_id] = true;
                $project_blob = new ReportBlob($blob_type, $year, $ni->getId(), $project_id);
                $milestone_data = array();
                if($project_blob->load($rep_addr)){
                    $milestone_data = $project_blob->getData();
                }
                //echo "NI=".$ni->getNameForForms()."<br >";
                foreach ($milestone_data as $k => $a){
                    //echo "ID=$k; ";
                    if( isset($a['comment']) && !empty($a['comment']) ){
                        $milestone_ni_comments[$k] = ( isset($milestone_ni_comments[$k]) )? $milestone_ni_comments[$k] : "";
                        $milestone_ni_comments[$k] .= $ni->getNameForForms() . ":<br /><i style='margin:10px;display:block;'>" . 
                            $a['comment'] . "</i><br />";
                            //echo "exists";
                    }
                    //echo "<br>";
                }
            }
        }

        $milestones = $project->getMilestonesDuring(REPORTING_YEAR);
        
        $html = "<div style='margin-bottom:20px;' class='pdfnodisplay'>You can edit the project milestones at <a href='{$project->getURL()}' target='_blank'><b>{$project->getName()}</b></a></div>";
        /*$dataUrl = "$wgServer$wgScriptPath/index.php?action=getProjectMilestoneTimelineData&project={$project->getId()}&year=".REPORTING_YEAR;
        $timeline = new Simile($dataUrl);
        $timeline->interval = "50";
        $timeline->popupWidth = "500";
        $timeline->popupHeight = "300";
        $wgOut->addScript("<script type='text/javascript'>
            var firstTimeLoaded = true;
            function toggleTimeline(){
                $('#milestoneTimeline').toggle();
                if($('#timelineButton').html() == 'Show Milestone Timeline'){
                    $('#timelineButton').html('Hide Milestone Timeline');
                    if(firstTimeLoaded){
                        onLoad{$timeline->index}();
                        firstTimeLoaded = false;
                    }
                }
                else{
                    $('#timelineButton').html('Show Milestone Timeline');
                }
            }
        </script>");
        $html .= "<a id='timelineButton' class='button pdfnodisplay' onClick=\"toggleTimeline();\">Show Milestone Timeline</a><div id='milestoneTimeline' class='pdfnodisplay' style='display:none;'>".$timeline->show()."</div>";
        */
        foreach($milestones as $milestone){
            $ni_comments = "";
            $key = $milestone->getMilestoneId();
            $title = $milestone->getTitle();
            $description = nl2br($milestone->getDescription());
            $assessment = nl2br($milestone->getAssessment());
            $start_date = date_parse($milestone->getVeryStartDate());
            $end_date = date_parse($milestone->getProjectedEndDate());
            $status = $milestone->getStatus();
            
            $dialog_id = "ni_comments_".$key."_dialog";
            
            $ni_comments = (isset($milestone_ni_comments[$key]))? $milestone_ni_comments[$key] : "";

            //Set up the dialog that shows milestone comments
            if ($ni_comments){
                $custom_js .= "$(\"#$dialog_id\").dialog({ autoOpen: false, height: 300, width: 500 });";
                $ni_comments =<<<EOF
                 <p class="pdf_hide"><a style="font-style:italic; font-weight:bold;" onclick="$('#$dialog_id').dialog('open'); return false;" href="#">See NI Comments</a></p>
                 <div class="pdf_hide" title="NI Milestone Comments" style="white-space: pre-line;" id="$dialog_id">$ni_comments</div>
EOF;
            }
            
            //Get the history of the milestone
            $history_html = "";
            $parents = array();
        
            $m_parent = $milestone;
            while(!is_null($m_parent)){
                $parents[] = $m_parent;
                $m_parent = $m_parent->getParent();
            }
            $parents = array_reverse($parents);
            
            foreach($parents as $m_parent){    
                $p_status = $m_parent->getStatus();
                if($p_status == "Continuing"){
                    continue;
                }
                $changed_on = $m_parent->getStartDate();
                $p_title = $m_parent->getTitle();
                $p_end_date = $m_parent->getProjectedEndDate();
                $p_description = nl2br($m_parent->getDescription());
                $p_assessment = nl2br($m_parent->getAssessment());
                $p_comment = nl2br($m_parent->getComment());
                if($p_comment){
                    $p_comment = "<br /><strong>Comment:</strong> $p_comment";
                }
                if($p_status == "New"){
                    $label = "Created";
                }
                else{
                    $label = $status;
                }
                
                $peopleInvolved = array();
                foreach($m_parent->getPeople() as $person){
                    $peopleInvolved[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                }
                $people = "";
                if(count($peopleInvolved) > 0){
                    $people = implode(", ", $peopleInvolved);
                    $people = "<strong>People Involved:</strong> $people<br />";
                }
                
                $lastEdit = "";
                if($m_parent->getEditedBy() != null && $m_parent->getEditedBy()->getName() != ""){
                    $lastEdit = "<strong>Last Edited By:</strong> <a href='{$m_parent->getEditedBy()->getUrl()}'>{$m_parent->getEditedBy()->getNameForForms()}</a><br />";
                }
                
                $history_html .=<<<EOF
                 <div style="padding: 10px; 0;"> 
                 <strong>$label</strong> on $changed_on<br />
                 <strong>Projected End Date:</strong> $p_end_date<br />
                 <strong>Title:</strong> $p_title<br />
                 $people
                 <strong>Description:</strong> $p_description<br />
                 <strong>Assessment:</strong> $p_assessment
                 $p_comment<br />
                 $lastEdit
                 </div>
                 <hr />    
EOF;
            }
            if($history_html != ""){
                $history_dialog_id = "history_m_$key";
                $custom_js .= "$(\"#$history_dialog_id\").dialog({ autoOpen: false, height: 600, width: 800 });";
                $history_html =<<<EOF
                <a class="pdf_hide" style="font-style:italic; font-weight:bold;" href="#" onclick="$('#$history_dialog_id').dialog('open'); return false;">See Milestone History</a>
                <div class="pdf_hide" title="Milestone History" style="white-space: pre-line;" id="$history_dialog_id">$history_html</div>
EOF;
            }
            
            $peopleInvolved = array();
            foreach($milestone->getPeople() as $person){
                $peopleInvolved[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
            }
            $people = "";
            if(count($peopleInvolved) > 0){
                $people = implode(", ", $peopleInvolved);
                $people = "<tr>
                            <td align='right' valign='top'><b>People Involved:</b></td>
                            <td>{$people}</td>
                        </tr>";
            }
            
            $lastEdit = "";
            if($milestone->getEditedBy() != null && $milestone->getEditedBy()->getName() != ""){
                $lastEdit = "<tr>
                            <td align='right' valign='top'><b>Last Edited By:</b></td>
                            <td><a href='{$milestone->getEditedBy()->getUrl()}'>{$milestone->getEditedBy()->getNameForForms()}</a></td>
                        </tr>";
            }
            
            $html .=<<<EOF
                <fieldset>
                <legend><b>$title</b></legend>
                <table>
                    <tr>
                        <td align='right' valign='top'><b>Start&nbsp;Date:</b></td>
                        <td>{$months[$start_date['month']]}, {$start_date['year']}</td>
                    </tr>
                    <tr>
                        <td align='right' valign='top'><b>Projected&nbsp;End&nbsp;Date:</b></td>
                        <td>{$months[$end_date['month']]}, {$end_date['year']}</td>
                    </tr>
                    <tr>
                        <td style='vertical-align:top;' align='right'><b>Status:</b></td>
                        <td>$status</td>
                    </tr>
                    $people
                    <tr>
                        <td align='right' valign='top'><b>Description:</b></td>
                        <td>{$description}</td>
                    </tr>
                    <tr>
                        <td align='right' valign='top'><b>Assessment:</b></td>
                        <td>{$assessment}</td>
                    </tr>
                    $lastEdit
                </table>
                <br />
                $history_html $ni_comments
                </fieldset>
EOF;
        }// milestones loop

        $wgOut->addHTML("$html");
        $wgOut->addHTML("</div>");
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
	}
    
    //Report version for HQP's
    static function ProjectReportTab(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $project, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
	    $p_name = $project->getName();
        $p_id = $project->getId();
        //$p_link = $p_name;
        
		$blob_type = BLOB_TEXT;
		$rptype = RP_LEADER;
		
    	$nce_activity_types = array(
	        LDR_RESACT_EXCELLENCE => "A. Excellence of the Research Program",
	        LDR_RESACT_HQPDEV => "B. Development of Highly Qualified Personnel",
	        LDR_RESACT_NETWORKING => "C. Networking and Partnerships",
	        LDR_RESACT_KTEE => "D. Knowledge and Technology Exchange and Exploitation",
	        LDR_RESACT_OTHEROUTCOMES => "E. Other Project Outcomes"     
	    );
	    
	    $other_activity_types = array(
	        //LDR_RESACT_OTHEROUTCOMES => "E. Other Project Outcomes",
	        LDR_RESACT_NETBENEFITS => "Benefits from being involved in the Network",
	        LDR_RESACT_NEXTPLANS => "Plans for Next Year"	        
	    );
        
        $ni_activity_types = array(
	        LDR_RESACT_EXCELLENCE => RES_RESACT_EXCELLENCE,
	        LDR_RESACT_HQPDEV => RES_RESACT_HQPDEV,
	        LDR_RESACT_NETWORKING => RES_RESACT_NETWORKING,
	        LDR_RESACT_KTEE => RES_RESACT_KTEE
	    );
        
		$rep_addr = ReportBlob::create_address($rptype, LDR_RESACTIVITY, LDR_RESACT_OVERALL, 0);
		$overall_activity_blb = new ReportBlob($blob_type, $year, 0, $p_id);

	    //Form submit processing
	    $report_submit = ($_POST && isset($_POST['report']))? $_POST['report'] : "";
	    if( $report_submit === "Save" ){
            
            //Save overall activity
            $overall_activity = $_POST['overall_activity'];
	        $overall_activity_blb->store($overall_activity, $rep_addr);
            
	        $activities = $_POST['activities'];
	        foreach( $activities as $a_type => $comment){   
	            $a_rep_addr = ReportBlob::create_address($rptype, LDR_RESACTIVITY, $a_type, 0);
                $blob = new ReportBlob($blob_type, $year, 0, $p_id);
                $blob->store($comment, $a_rep_addr);
                //echo $comment."<br>";
	        }
	    }

	    //Fetch the previously saved data, if exists
	    $overall_activity_blb->load($rep_addr);
    	$overall_activity = $overall_activity_blb->getData();

	    //Render the page
	    $wgOut->setPageTitle("Project Report: $p_name");
	    $pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    
	    //$person = Person::newFromId($reporteeId);

	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () { 
    		     $('#overall_activity').limit('3600','#charsLeft_overall_activity'); 
    		     $('#lnk_comments_overall').click(function(e) {
                       e.preventDefault();        
                       $('#div_comments_overall').toggle();
                  });              
EOF;

        $activity_overview_html =<<<EOF
            <div id="ldr_report_wrapper">
            <h2>I. Executive Summary</h2>
            <br />
            <h3><a href="#" id="lnk_comments_overall">Overview of Activity</a></h3>
            <div class="pdf_show" style="display:none;" id="div_comments_overall">
                <p class="pdf_hide"><span class="curr_chars">(currently <span id="charsLeft_overall_activity">0</span> chars out of allowed 3600.)</span></p>
                <textarea id="overall_activity" rows="15" style="" 
                    name="overall_activity">$overall_activity</textarea><br />   
            </div>
EOF;

        $wgOut->addHTML($activity_overview_html);  


	    $activities_html = "<br /><h2>II. NCE Criteria</h2><br />";
	    //NCE ACTIVITIES
	    foreach ($nce_activity_types as $a_type => $a_lbl){
	        $a_rep_addr = ReportBlob::create_address($rptype, LDR_RESACTIVITY, $a_type, 0);

	        //Links, divs and triggers
	        $lnk_id = "lnk_comments_$a_type";
            $div_id = "div_comments_$a_type";
            
            $custom_js .=<<<EOF
                $('#$lnk_id').click(function(e) {
                      e.preventDefault();        
                      $('#$div_id').toggle();
                 });      
EOF;
            

            $a_link = '<a id="'.$lnk_id.'" href="#">'.$a_lbl.'</a>';
            
	        $activities_html .=<<<EOF
	            <h3>$a_link</h3>
	            <div class="pdf_show" style="display: none;" id="$div_id">
EOF;
            
            //Load NI comments if they exist
            $ni_pr_activity_comments = "";
    	    if( isset($ni_activity_types[$a_type]) ){
    	        $ni_rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_RESACTIVITY, $ni_activity_types[$a_type], 0);
    	        
    	        $ni_objs = array_merge( $project->getAllPeopleDuring("CNI"), $project->getAllPeopleDuring("PNI") );
                foreach ($ni_objs as $h){
                    $ni_blob = new ReportBlob($blob_type, $year, $h->getId(), $p_id);
                    $ni_blob->load($ni_rep_addr);
                    $ni_comment = $ni_blob->getData();
                    if($ni_comment){
                        $ni_pr_activity_comments .= $h->getNameForForms() . ":<br /><i style='margin:10px;display:block;'>".
                            $ni_comment . "</i><br />";
                    }
		            
                }
                $ni_comm_dialog_id = "nicomm_".$a_type."_".$p_id;
                $custom_js .= "$(\"#$ni_comm_dialog_id\").dialog({ autoOpen: false, height: 300, width: 500 });";
    	    }
    	       

            //Load previously saved data
            $p_blob = new ReportBlob($blob_type, $year, 0, $p_id);
            $p_blob->load($a_rep_addr);
            $p_blob_data = $p_blob->getData();
            
            $custom_js .= "$('#activities\\\[$a_type\\\]').limit('1800','#charsLeft_activities\\\[$a_type\\\]');";
            
            $activities_html .=<<<EOF
                <div>
                <p class="project_header pdf_hide"><span class="curr_chars">
                    (currently <span id="charsLeft_activities[$a_type]">0</span> chars out of allowed 1800.)
                </span></p>
                <textarea rows="10" style="" id="activities[$a_type]"  
                    name="activities[$a_type]">$p_blob_data</textarea>
EOF;
            if($ni_pr_activity_comments){
                $activities_html .=<<<EOF
                <a class="pdf_hide" style="font-style:italic; font-weight:bold; float:right;" href="#" onclick="$('#$ni_comm_dialog_id').dialog('open'); return false;">See NI Comments</a>
                <div class="pdf_hide" title="NI Comments" style="white-space: pre-line;" id="$ni_comm_dialog_id">$ni_pr_activity_comments</div>
EOF;
            }
            
            $activities_html .= "</div><br />";
            
            $activities_html .= "</div>";     

	    }
	    
	    //OTHER ACTIVITIES
	    $i=0;
	    foreach ($other_activity_types as $a_type => $a_lbl){
	        $a_rep_addr = ReportBlob::create_address($rptype, LDR_RESACTIVITY, $a_type, 0);
            
            if($i == 0){
                $activities_html .= "<br /><h2>III. Supplemental</h2><br />";
                $custom_js .= "$('#activities\\\[$a_type\\\]').limit('600','#charsLeft_activities\\\[$a_type\\\]');";
                $allowed = 600;
            }
            else{// if($i == 2){
                $custom_js .= "$('#activities\\\[$a_type\\\]').limit('600','#charsLeft_activities\\\[$a_type\\\]');";
                $allowed = 600;
            }
            //else{
            //    $custom_js .= "$('#activities\\\[$a_type\\\]').limit('1800','#charsLeft_activities\\\[$a_type\\\]');";
            //    $allowed = 1800;
            //}
            
	        //Links, divs and triggers
	        $lnk_id = "lnk_comments_$a_type";
            $div_id = "div_comments_$a_type";
            
            $custom_js .=<<<EOF
                $('#$lnk_id').click(function(e) {
                      e.preventDefault();        
                      $('#$div_id').toggle();
                 });
EOF;
            

            $a_link = '<a id="'.$lnk_id.'" href="#">'.$a_lbl.'</a>';
	        
	        $activities_html .= "<h3>$a_link</h3><div class='pdf_show' style='display: none;' id=\"$div_id\">";
	        $activities_html .= "<p class='pdf_hide'><span class='curr_chars'>
                (currently <span id='charsLeft_activities[$a_type]'>0</span> chars out of allowed $allowed.)</span></p>";
                
	        $a_blob = new ReportBlob($blob_type, $year, 0, $p_id);
            $a_blob->load($a_rep_addr);
            $a_blob_data = $a_blob->getData();
            
            $rows = $allowed/120;
            $activities_html .=<<<EOF
                <textarea rows="$rows" style="" id="activities[$a_type]" name="activities[$a_type]">$a_blob_data</textarea>
                </div>
EOF;
	    $i++;
	    }
	    
	    $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);

	    $wgOut->addHTML($activities_html."</div>");   
	}
	
	//NI Comments Tab
    static function NICommentsTab(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $project, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
	    $p_name = $project->getName();
        $p_id = $project->getId();
        //$p_link = $p_name;
        
		$blob_type = BLOB_ARRAY;
		$rptype = RP_LEADER;
		
		$nce_activity_types = array(
	        RES_RESACT_EXCELLENCE => "A. Excellence of the Research Program",
	        RES_RESACT_HQPDEV => "B. Development of Highly Qualified Personnel",
	        RES_RESACT_NETWORKING => "C. Networking and Partnerships",
	        RES_RESACT_KTEE => "D. Knowledge and Technology Exchange and Exploitation"        
	    );
		
	    $rep_addr = ReportBlob::create_address($rptype, LDR_NICOMMENTS, 0, 0);
		$nicomments_blb = new ReportBlob($blob_type, $year, 0, $p_id);

	    //Form submit processing
	    $report_submit = ($_POST && isset($_POST['nicomments']))? $_POST['nicomments'] : "";
	    if( $report_submit === "Save" ){
            
	        $comments = $_POST['comments'];
            $nicomments_blb->store($comments, $rep_addr);
	    }

	    //Fetch the previously saved data, if exists
	    $nicomments_blb->load($rep_addr);
    	$comments = $nicomments_blb->getData();
	    
	    //Render the page
	    $wgOut->setPageTitle("Comments: $p_name");
	    $url_prefix = "$wgServer$wgScriptPath/index.php/";
	    
	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () {\n
EOF;

        $comments_html =<<<EOF
            <div id="ldr_comments_wrapper">
            <h2>Comments</h2>
            <br />
EOF;
        
        //Loop through all NI's and add a comment box
        $proj_ldr_ids = $project->getLeaders(true);
	    $proj_cldr_ids = $project->getCoLeaders(true);
	    $pl_ids = array_merge($proj_ldr_ids, $proj_cldr_ids);
	    
	    foreach ($pl_ids as $pl){
	        $pl_id = $pl;
	        $pl = Person::newFromId($pl_id);
	        $pl_role =$pl->getType();
	        $pl_username = $pl->getName();
	        //$pl_name = $pl->getNameForForms();
	        $pl_name = preg_split('/\./', $pl->getName(), 2);
            $pl_name = $pl_name[1].", ".$pl_name[0];
	        if(in_array($pl_id, $proj_ldr_ids)){
	            $pl_cpl = "PL";
	        }else{
	            $pl_cpl = "Co-PL";
	        }
	        //$person_link = "<a target='_blank' href='{$url_prefix}{$pl_role}:{$pl_username}'>$pl_name ($pl_cpl)</a>";
    	    
    	    $comments_html .= "<h3>$pl_name ($pl_cpl)</h3>";
	    }
	    
        $pni_objs = $project->getAllPeopleDuring(PNI);
        $cni_objs = $project->getAllPeopleDuring(CNI);
        $ni_objs = array_merge($pni_objs, $cni_objs);
        
        //Sort people by name
        $people_sorted = array();
        foreach ($ni_objs as $p){
            $p_name = preg_split('/\./', $p->getName(), 2);
            $p_name = $p_name[1].", ".$p_name[0];
            $people_sorted[$p_name] = $p->getId();
        }
        ksort($people_sorted);
        $ni_objs = array();
        foreach($people_sorted as $p => $i){
            $ni_objs[] = Person::newFromId($i);
        }
        
	    foreach ($ni_objs as $ni){
            $ni_id = $ni->getId();
            if(in_array($ni_id, $pl_ids)){
                continue;
            }
            
            $ni_username = $ni->getName();
            //$ni_name = $ni->getNameForForms();
            $ni_name = preg_split('/\./', $ni->getName(), 2);
            $ni_name = $ni_name[1].", ".$ni_name[0];
            $ni_role = ($ni->isCNI())? "CNI" : (($ni->isPNI())? "PNI" : $ni->getType());
            
	        //Links, divs and triggers
	        $lnk_id = "lnk_comments_$ni_id";
            $div_id = "div_comments_$ni_id";
            
            $custom_js .=<<<EOF
                $('#$lnk_id').click(function(e) {
                      e.preventDefault();        
                      $('#$div_id').toggle();
                 });\n
EOF;
            
            $nce_comments = "";
            foreach ($nce_activity_types as $a_type => $a_lbl){
    	        $a_rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_RESACTIVITY, $a_type, 0);
                $blob = new ReportBlob(BLOB_TEXT, $year, $ni_id, $p_id);
                $blob->load($a_rep_addr);
                $blob_data = $blob->getData();
                if($blob_data){
                    $nce_comments .=<<<EOF
                        <strong>$a_lbl</strong><br />
                        <p>$blob_data</p>
EOF;
                }
	        }
	        
	        $preview_lnk_id = "lnk_nce_comments_$ni_id";
            $preview_div_id = "div_nce_comments_$ni_id";
            $preview_link = "";
            
            if($nce_comments){
                $preview_link = "<span class='pdf_hide' style='font-size:11px;'><a id='$preview_lnk_id' href='#' onclick='$(\"#$preview_div_id\").dialog(\"open\"); return false;'>NCE Comments</a></span>";
                $custom_js .=<<<EOF
                    $('#$preview_div_id').dialog({ autoOpen: false, height: 500, width: 600 });
EOF;
            }
            
            $c_link = "<a id='$lnk_id' href='#'>$ni_name ($ni_role)</a>";
            
	        $comments_html .=<<<EOF
	            <h3>$c_link&nbsp;&nbsp; $preview_link</h3>
	            <div title="NCE Comments" class="pdf_hide" style="white-space: pre-line;" id="$preview_div_id">$nce_comments</div>
	            <div class='pdf_show' style="display: none;" id="$div_id">
EOF;
            
            $custom_js .= "$('#comments\\\[$ni_id\\\]').limit('600','#charsLeft_comments\\\[$ni_id\\\]');\n";

            $comment = (isset($comments[$ni_id]))? $comments[$ni_id] : "";
            
            $comments_html .=<<<EOF
                <p class="project_header pdf_hide"><span class="curr_chars">
                    (currently <span id="charsLeft_comments[$ni_id]">0</span> chars out of allowed 600.)
                </span></p>
                <textarea rows="10" style="" id="comments[$ni_id]"  
                    name="comments[$ni_id]">$comment</textarea>
                <br />
EOF;

                $comments_html .= "</div>";
        
        }
        
        $comments_html .= "</div>";
	    $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
	    $wgOut->addHTML($comments_html);
	}	
	
	//NI Comments Tab
    static function BudgetTab(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $project, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
	    $p_name = $project->getName();
        $p_id = $project->getId();
        //$p_link = $p_name;
        
		$blob_type = BLOB_TEXT;
		$rptype = RP_LEADER;
		
		$rep_addr = ReportBlob::create_address($rptype, LDR_BUDGETJUSTIF, 0, 0);
		$justif_blb = new ReportBlob($blob_type, $year, 0, $p_id);
		
		//Form submit processing
	    $report_submit = ($_POST && isset($_POST['budget']))? $_POST['budget'] : "";
	    if( $report_submit === "Save" ){
            
            //Save overall activity
            $justif = $_POST['budget_justif'];
	        $justif_blb->store($justif, $rep_addr);
	    }

	    //Fetch the previously saved data, if exists
	    $justif_blb->load($rep_addr);
    	$justif = $justif_blb->getData();
		
		//Render the page
	    $wgOut->setPageTitle("Budget: $p_name");
	    //$pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    
	    //$person = Person::newFromId($reporteeId);

	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () { 
    		     $('#budget_justif').limit('600','#charsLeft_budget_justif');              
EOF;

        $justif_html =<<<EOF
            <div id="ldr_budget_wrapper">
            
            <h3>Budget Justification</h3>
            <div id="div_budget_just">
                <p class="pdf_hide"><span class="curr_chars">(currently <span id="charsLeft_budget_justif">0</span> chars out of allowed 600.)</span></p>
                <textarea id="budget_justif" rows="6" style="" 
                    name="budget_justif">$justif</textarea><br />   
            </div>
EOF;

        $wgOut->addHTML($justif_html);
        
        $budget = $project->getRequestedBudget($year);
        if($budget != null){
            $wgOut->addWikiText("== Budget ==
                                 __NOEDITSECTION__\n");
            $wgOut->addHTML($budget->render());
        }
        
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
        $wgOut->addHTML("</div>");
        
	}	
	
	//get comments from NI
    static function getNIComments(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    $uid = $reporteeId;
	    $blob_type = BLOB_ARRAY;
		$rptype = RP_LEADER;
		
	    $person = Person::newFromId($reporteeId);
	    $projects = $person->getProjects();
	    
	    $rep_addr = ReportBlob::create_address($rptype, LDR_NICOMMENTS, 0, 0);
	    
	    $comments_html = "<center><h1>Project Leader Comments</h1></center>";
	    
	    foreach ($projects as $project){
	        
    	    $p_name = $project->getName();
            $p_id = $project->getId();
		
    		$comments_html .= "<h3>$p_name</h3>";
		
    		//Fetch the coomment blobs from all Project leaders/co-leaders
            $proj_ldr_ids = $project->getLeaders(true);
    	    $proj_cldr_ids = $project->getCoLeaders(true);
    	    $pl_ids = array_merge($proj_ldr_ids, $proj_cldr_ids);
    	    
    	    //Sort people by name
            $people_sorted = array();
            foreach ($pl_ids as $p){
                $p = Person::newFromId($p);
                $p_name = preg_split('/\./', $p->getName(), 2);
                $p_name = $p_name[1].", ".$p_name[0];
                $people_sorted[$p_name] = $p->getId();
            }
            ksort($people_sorted);
            $pl_ids = array();
            foreach($people_sorted as $p=>$i){
                $pl_ids[] = $i;
            }
    	
			
    	    foreach ($pl_ids as $pl_id){
	            
    	        if(in_array($pl_id, $proj_ldr_ids)){
    	            $pl_cpl = "PL";
    	        }else{
    	            $pl_cpl = "Co-PL";
    	        }
	        
    	        $pl = Person::newFromId($pl_id);
    	        //$pl_name = $pl->getNameForForms();
	            $pl_name = preg_split('/\./', $pl->getName(), 2);
                $pl_name = $pl_name[1].", ".$pl_name[0];
    		    
	            
	            
    	        $comments_html .=<<<EOD
    	            <span style="font-weight:bold;">$pl_name ($pl_cpl)</span><br />
EOD;
            }
			
			$comment_str = "";
            $nicomments_blb = new ReportBlob($blob_type, $year, 0, $p_id);
		    $nicomments_blb->load($rep_addr);
		    $comments = $nicomments_blb->getData();
			$comment = (isset($comments[$uid]))? $comments[$uid] : "";
			$comment = nl2br($comment);
            $comment_str .= "<p>$comment</p>";
			
			$comments_html .= $comment_str;
	    
        }
        
        return $comments_html;
	    
	}
	
	static function SubmitReport(){
        global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $reportList, $project, $reporteeId;
        //$this->_extradbg = "";
    	$submit_status = "";
    	$project_name = $project->getName();
    	$project_id = $project->getId();
    	$person = Person::newFromId($reporteeId);
        $person_name = $person->getName();
        
        $submit_action = "";
        if($_POST){
            if(isset($_POST['action_type']) && $_POST['action_type'] != '' ){
                $submit_action = $_POST['action_type'];
            }
        }
        $sto = new ReportStorage($person);
    	switch ($submit_action) {
    	case 'download_report':
    		$tok = ($_POST && isset($_POST['pdftoken']))? $_POST['pdftoken'] : "";
    		if (! empty($tok)) {
    			$pdf = $sto->fetch_pdf($tok, false);
    			$len = $sto->metadata('len_pdf');
    			if ($pdf === false || $len == 0) {
    				$wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
    			}
    			else {
    				$tst = $sto->metadata('timestamp');
    				// Make timestamp usable in filename.
    				$tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
    				$name = $project_name."_Overview_".$person_name. "-{$tst}.pdf";
    				if ($len == 0) {
    					// No data, or no report at all.
    					$wgOut->addHTML("No reports available for download.");
    					return false;
    				}
    				// Good -- transmit it.
    				$wgOut->disable();
    				ob_clean();
    				header('Content-Type: application/pdf');
    				header('Content-Length: ' . $len);
    				header('Content-Disposition: attachment; filename="'.$name.'"');
    				header('Cache-Control: private, max-age=0, must-revalidate');
    				header('Pragma: public');
    				ini_set('zlib.output_compression','0');
    				echo $pdf;
    				return true;
    			}
    		}
    		break;
    		
    	    case 'download_report_com':
        		$tok = ($_POST && isset($_POST['pdftoken_com']))? $_POST['pdftoken_com'] : "";
        		if (! empty($tok)) {
        			$pdf = $sto->fetch_pdf($tok, false);
        			$len = $sto->metadata('len_pdf');
        			if ($pdf === false || $len == 0) {
        				$wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
        			}
        			else {
        				$tst = $sto->metadata('timestamp');
        				// Make timestamp usable in filename.
        				$tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
        				$name = $project_name."_Comments_".$person_name. "-{$tst}.pdf";
        				if ($len == 0) {
        					// No data, or no report at all.
        					$wgOut->addHTML("No reports available for download.");
        					return false;
        				}
        				// Good -- transmit it.
        				$wgOut->disable();
        				ob_clean();
        				header('Content-Type: application/pdf');
        				header('Content-Length: ' . $len);
        				header('Content-Disposition: attachment; filename="'.$name.'"');
        				header('Cache-Control: private, max-age=0, must-revalidate');
        				header('Pragma: public');
        				ini_set('zlib.output_compression','0');
        				echo $pdf;
        				return true;
        			}
        		}
        		break;	

    	case 'submit_report':
    		//$subconf = ($_POST && isset($_POST['finalsubmissioncheck']))? $_POST['finalsubmissioncheck'] : "";
    		
    		/*if ($subconf === false) {
    			// Checkbox not selected.
    			$submit_status = "<tr><td style='background-color: #FF6347'>In order to successfully submit, you need to mark the submission checkbox,<br />asserting that you have reviewed the report generated.\n";
    		}
    		else {*/
    		// Try to mark report as submitted, and generate a status message.
			//$tok = Report::post_field($_POST, 'markrptok');
			$tok = ($_POST && isset($_POST['markrptok']))? $_POST['markrptok'] : "";
			if ($tok === false) {
				$submit_status = "<tr><td style='background-color: #FF6347'>Report not found. Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.\n";
			}
			else {
				$sto->select_report($tok, false);
				switch ($sto->mark_submitted_ns($tok)) {
				case 0:
					$submit_status = "<tr><td style='background-color: #FF6347'>Individual report ID #<tt>{$tok}</tt> could not be marked as submitted. Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.\n";
					break;

				case 1:
					$submit_status = "<tr><td style='background-color: #90EE90'>Report successfully submitted.\n";
					break;

				case 2:
					$submit_status = "<tr><td style='background-color: #FF6347'>Report was already marked as submitted.\n";
					break;
				}
			}
    		//}
    		break;
    	} //action check


        //Page processing
        
        $tok = false;
        $tok_com = false;
        $tst = '';
        $tst_com = '';
        $len = 0;
        $sub = 0;
        $len_com = 0;
        $sub_com = 0;
    	
    	//$check = $sto->list_reports_current($person->getId(), 1, 0, 2);
    	$check = $sto->list_project_reports($project_id, 1, 0, 2);
    	if (count($check) > 0) {
    		$tok = $check[0]['token'];
    		$sto->select_report($tok, false);    	
    		$tst = $sto->metadata('timestamp');
    		$len = $sto->metadata('len_pdf');
    		$sub = $sto->metadata('submitted');	
    	}
    	//$check2 = $sto->list_reports_current($person->getId(), 1, 0, 7); //Comments report
    	$check2 = $sto->list_project_reports($project_id, 1, 0, RPTP_LEADER_COMMENTS); //Comments report
    	if (count($check2) > 0) {
    		$tok_com = $check2[0]['token'];
    		$sto->select_report($tok_com, false);    	
    		$tst_com = $sto->metadata('timestamp');
    		$len_com = $sto->metadata('len_pdf');
    		$sub_com = $sto->metadata('submitted');	
    	}
    	//$tok = $sto->metadata('token');
        //generateHQPReportsHTML($person, 2011);
        $chunk = "<input type='hidden' id='action_type' name='action_type' value='' />";

    	$chunk .= "<h4>1. Generate a new report for submission</h4>
    <p>Generate a report with the data submitted: <button type='button' onclick='javascript:generateReport();' name='ni_generate_report' value='ni_generate_report'>Generate report</button></p>
    <h4>2. Download the report submitted for reviewing</h4>\n";

    	// Present some data on available reports.
    	if ($tok === false) {
    		// No reports available.
    		$style1 = "";
    		$style2 = "display:none;";
    	}
    	else {
    	    $style2 = "";
    		$style1 = "display:none;";
    	}    
		
		//echo "SUB=$sub; $tok";
		$subm = "";
		if ($sub == 1) {
			$subm = "Yes";
			$subm_style = "background-color:#008800;";
		}
		else {
			$subm = "No";
			$subm_style = "background-color:red;";
		}
		$chunk .= 
		"<p><table cellspacing='8'>
         <tr>
         <th>Identifier</th>
         <th>Generated (GMT " . date('P') . ")</th>
         <th>Download</th>
         <th>Submitted?</th>
         </tr>
         <tr>
         <td><tt id='ex_token'>{$tok}</tt></td>
         <td id='ex_time'>{$tst}</td>
         <td>
         <input id='ex_token2' type='hidden' name='pdftoken' value='{$tok}'/>
         <span style='$style1' id='no_download_button'>No Overview PDF Available</span>
         <button style='$style2' id='download_button' onclick='javascript:submitReportAction(\"download_report\");'>
               Download Overview as PDF
         </button>
         </td>
         <td align='center' id='submit_status_cell' style='$subm_style'><b>$subm</b></td>
         </tr>
         <tr>
          <td><tt id='ex_token_com'>{$tok_com}</tt></td>
          <td id='ex_time_com'>{$tst_com}</td>
          <td>
          <input id='ex_token2_com' type='hidden' name='pdftoken_com' value='{$tok_com}'/>
          <span style='$style1' id='no_download_button_com'>No Comments PDF Available</span>
          <button style='$style2' id='download_button_com' onclick='javascript:submitReportAction(\"download_report_com\");'>
                Download Comments as PDF
          </button>
          </td>
          <td></td>
          </tr>
         </table></p>
         <h4>3. Submit the report</h4>
         <p>You can submit your report for evaluation. Make sure you review it before submitting.<br />Please note:</p>
         <ul>
         <li>If you need to make a correction to your report that is already submitted, you can generate and submit again.</li>
         <li>The most recent submission is used for evaluation.</li>
         <li>If no reports were submitted, the report most recently generated is used for evaluation.</li>
         <li>If you encounter any issues, please contact
         <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.</li>
         </ul></p>\n";
		
		$visibility = "display:none;";
		if ($sub == 1 || $tok === false || (FROZEN)) {
		    $visibility = "display:none;";
		}    
		
		$chunk .= 
		"<div id='report_submit_div' style='$visibility'><p>
        <table border='0' style='margin-top: 20px;' cellpadding='10'>
        <tr><td style='background-color: #E6E6FA'>
        <input type='hidden' id='markrptok' name='markrptok' value='{$tok}'>
        <input type='hidden' id='markrptok_com' name='markrptok_com' value='{$tok_com}'>
        <!--input type='checkbox' name='finalsubmissioncheck' /-->
        
        <button type='button' onclick='javascript:submitReportAction(\"submit_report\");'>
            Submit final report
        </button>
        </td></tr>
        </table>
        $submit_status
        </p></div>";
		

		// Some space.
		$chunk .= "<p>&nbsp;</p>";
    	

    //		$dbg = (array)$sto->fetch_data($tok);
    //		$chunk .= "<h4>Debugging</h4>\n<pre>\nExtra debug:\n{$this->_extradbg}\nPDF Data:\n" . print_r($dbg, true) . "</pre>\n";

    	$wgOut->setPageTitle("Review & Submit: {$project_name}");
    	$wgOut->addHTML($chunk);
    }
	
	//No access page
    static function PermissionDenied(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
        //Render the page
	    $wgOut->setPageTitle("Permission Denied");
        
        $wgOut->addHTML("<p>You do not have permissions to access this report. If you're a Manager and are trying to view somebody else's report, please make sure that you include proper parameters in the URL.</p>");
    
    }

     private function sort_people_by_last_name($arr){
            $to_sort = array();
            foreach($arr as $a){
                $person = Person::newFromId($a);
                $name = preg_split('/\./', $person->getName(), 2);
                $name = $name[1].", ".$name[0];
                $to_sort[$name] = $a;
            }
            ksort($to_sort);
            
            $arr = array();
            foreach($to_sort as $name => $id){
                $arr[] = $id;
            }
            return $arr;
            
        }
        
    private function sort_papers_by_type($arr){
        $to_sort = array();
        foreach($arr as $a){
            $paper = Paper::newFromId($a);
            $type = $paper->getType();
            $to_sort[$a] = $type;
        }
        asort($to_sort);

        $arr = array();
        foreach($to_sort as $id => $type){
            $arr[] = $id;
        }
        return $arr;
    }    
    
}
	
?>
