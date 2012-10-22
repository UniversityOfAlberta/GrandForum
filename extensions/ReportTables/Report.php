<?php

autoload_register('ReportTables');

//require_once("ReportStorage.php");
//require_once("ReportIndex.php");
/*
require_once("ReviewerIndex.php");
require_once("Evaluate.php");
require_once("EvaluatorIndex.php");
require_once("Dashboard.php");
require_once("ProjectReport.php");
*/
// Not exactly required, but closely related:

/*
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['OldReport'] = 'OldReport'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['OldReport'] = $dir . 'OldReport.i18n.php';
$wgSpecialPageGroups['OldReport'] = 'reporting-tools';

$reportList = array();

abstract class AbstractReportOld extends SpecialPage{

    var $reportName;
    var $initialized;

    function AbstractReportOld($reportName, $permissions){
        $this->reportName = $reportName;
        wfLoadExtensionMessages($reportName);
		SpecialPage::SpecialPage($reportName, $permissions, true);
    }
    
    // This function when implemented should define several constants, 
    // which form the high level structure of the report
    abstract function initReport();
    
    abstract function run();
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $generatePDF = (isset($_GET['generatePDF']));
        $evalPDF = (isset($_GET['evalPDF']));
        
        if(!$generatePDF && !$evalPDF){
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
        if(!$generatePDF && !$evalPDF){
            OldReport::printPreviousNext();
        }
        $this->run();
        if(!$generatePDF && !$evalPDF){
            OldReport::printPreviousNext();
        }
        if($generatePDF && $par != "noPDF"){
            if(isset($_GET['pdfType'])){
                $this->generatePDF($_GET['pdfType']);
            }
            else{
                $this->generatePDF();
            }
        }
        else if($evalPDF && $par != "noPDF"){
            $this->evalPDF();
        }
        
        if(!$generatePDF && !$evalPDF){
        $wgOut->addHTML("</form>");
        }
    }
    
    function evalPDF(){
        global $reportList, $wgOut, $reporteeId;
        ini_set("memory_limit","256M");
        $head = <<<EOF
EOF;
        $reportee = Person::newFromId($reporteeId);
        $reportee_name = $reportee->getName();
        $reportee_name_print = $reportee->getNameForForms();

        $wgOut->clearHTML();
        for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
            $page = $reportList[$i][0];
            if($page == "archive" || $page == "ni_submit"){
                // Don't display the archived pdfs page
                continue;
            }
            $_POST[$page] = true;
            $wgOut->addHTML("<center><h1>{$reportList[$i][1]}</h1></center>");
            $this->execute("noPDF");
            unset($_POST[$page]);
            $wgOut->addHTML("<div style='page-break-after:always;'></div>");
        }
        
        /*
         * INITIAL CLEANUP
         */
/*          
        $html = str_get_html($wgOut->getHTML(), true, true, DEFAULT_TARGET_CHARSET, false); // Create the dom object
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
        foreach($html->find("#instructions") as $inst){
            $inst->outertext = '';
        }
        $html->find("[class=basic_info]", 0)->style = 'margin-bottom:0;';
        foreach($html->find('[class=pdfh2]') as $pdfh2){
            $pdfh2->tag = 'h2';
            $pdfh2->outertext = $pdfh2->outertext;
        }
        foreach($html->find('[class=pdfproject_head]') as $p_head){
            $p_head->tag = 'b';
            $p_head->style = 'font-size:14px;';
            //$p_head->outertext = '';
            //$p_head->outertext = '<span style="font-size:14px;">'.$p_head->outertext.'</span>';
            //$p_head->style = 'font-size:14px;';
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
        
        $dashboards = $html->find('table.dashboard');
        $i = 0;
        foreach($dashboards as $dashboard){
            if(count($dashboards) == $i+1){
                $dashboard->style = str_replace("page-break-after:always;", "", $dashboard->style);
            }
            $i++;
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
       
        foreach ($html->find("input[class=short]") as $txt){
            $txt->outertext = ($txt->value)? $txt->value : 0;
            $txt->outertext = "<b style='margin-left:5px;'>".$txt->outertext."</b>";
            $parent = $txt->parent();
            $parent->innertext = $parent->last_child()->outertext;
            $parent->innertext .= "<b style='margin-left:10px;'>:</b> ".$txt->outertext;
        }
        
        if( $involved_since = $html->find("input#involved_since", 0)){
            $val = $involved_since->class;
            $involved_since->outertext = "<b>".$val."</b>";
        }
        if($involved_until = $html->find("input#involved_until", 0)){
            $val = $involved_until->value;
            $involved_until->outertext = "<b>".$val."</b>";
        }
        
        foreach ($html->find("[class=pdf_hide]") as $el){
            $el->outertext = "";
        }
        foreach ($html->find("[class=pdf_show]") as $el){
            $el->style = "display:block;";
        }
        
        foreach ($html->find(".milestones_project_hdr") as $hdr){
            $hdr->find("b", 0)->style = "font-size:14px; padding-right:25px;";
            $hdr->find("span", 0)->style = "font-size:14px;";
        }
        
        $html->find("table.milestones_hdr_table",0)->outertext = "";
        
        foreach ( $html->find("table.milestones") as $tbl ){
                foreach ($tbl->find("tr") as $tr ){
                    if($worked = $tr->find("td", 0)->find("input[checked=checked]", 0)){
                        $worked = $worked->value;
                    }else{
                        $worked = "No";
                    }
                    
                    $descr = "";
                    $comment = "";
                    if($tr->find("td", 1)){
                        if($tr->find("td", 1)->find("b",0)){
                            $descr = $tr->find("td", 1)->find("b",0)->innertext;
                        }
                    }
                    if($tr->find("td", 2)){
                        $comment = $tr->find("td", 2)->innertext;
                    }
                    
                    $tr->tag = "div";
                    $tr->style = "border-bottom: 1px solid #000; padding: 10px 0;";
                    $tr->innertext =<<<EOD
                        <b>Worked On? </b>$worked<br />
                        <b>Milestone:</b><br />$descr<br />
                        <b>Comments:</b><br />$comment
EOD;
                }
            $tbl->outertext = $tbl->innertext;
        }
        
        
        foreach($html->find(".nce_section") as $nce){
            $nce_id = $nce->id;
            $nce_hdr_id = 'lnk_'.substr($nce_id, 4);
            $nce->find(".section_char_count", 0)->style = "";
            $char_span = $nce->find(".section_char_count", 0)->outertext;
            $nce->find(".section_char_count", 0)->outertext = "";
            $html->find("#{$nce_hdr_id}", 0)->outertext = $html->find("#{$nce_hdr_id}", 0)->outertext." ".$char_span;
            //$nce->outertext = $char_span."<br />".$nce->outertext;
        }
        
        foreach($html->find(".project_header") as $hdr){
            $hdr->outertext = "<span style='font-style:italic;'>(".str_replace(array('&nbsp;',' '),'',$hdr->innertext).")</span> ";
        }
        
        $project_times = "";
        foreach($html->find(".milestones_project_hdr") as $h3){
            $project_times .= $h3->innertext."<br />";
        }
        
        //This removes the h1 tag for NI Questionnaire heading

        //$html->find("table[id=dashboard]", 0)->outertext = "";
        $html->find(".ni_questionnaire_wrapper", 0)->prev_sibling()->outertext .= $project_times. "<br />";
        
        //Remove questionnaire from where it is and save for adding it to the end of PDF
        $questionnaire = $html->find(".ni_questionnaire_wrapper", 0)->outertext;
        $html->find(".ni_questionnaire_wrapper", 0)->outertext = "";
        
        //Attach NI comments at the end of NI Report
        $ni_comments = ProjectReport::getNIComments();
        $html->find("#ni_report_wrapper", 0)->outertext .= "<div style='page-break-after:always;'></div>$ni_comments";
        //$html->find("#dashboard", 0)->next_sibling()->outertext = "";
        //$html->find("#dashboard2", 0)->next_sibling()->outertext = "";
        /*
         * BUDGET MANIPULATION
         */
/*
        $budget_just = $html->find('[id=div_budget_just]', 0);
        $budget_just2 = $html2->find('[id=div_budget_just]', 0);
        if($budget_just != null){
            $budget_just->outertext = '';
            $budget_just2->class = '';
            $budget_just2->find('p', 0)->outertext = '';
        }
        $budget = $html->find('[id=budget]', 0);
        if($budget != null){
            $budget->cellpadding = '1';
            $budget->cellspacing = '1';
            $budget->rules = null;
            $budget->boxes = null;
            $budget->frame = null;
            $budget->style = 'background-color:#000000;margin-bottom:15px;';
            $budget->width = '100%';
            foreach($budget->find('td') as $td){
                $td->nowrap = null;
                $td->style = 'background-color:#FFFFFF;';
                $td->colspan='1';
                if($td->find('b', 0) != null){
                    $td->innertext = "<small>".$td->plaintext."</small>";
                }
            }
            $budget->outertext = $budget->outertext.$budget_just2->innertext;
        }
        else if($budget_just != null){
            $budget_just->outertext = $budget_just2->outertext;
        }
        
        //Attach Dashboard details
        $details = "";
        foreach($html->find('.pdfDetailsDiv') as $div){
            $details .= $div->outertext;
            $div->outertext = "";
        }
        
        $html->find("#ni_budget_wrapper", 0)->outertext .= "<div style='page-break-after:always;'></div><center><h1>Appendix A (Dashboard Details)</h1></center>".$details."<div style='page-break-after:always;'></div><center><h1>Appendix B (Questionnaire)</h1></center>".$questionnaire;

        /*
         * ALL MANIPULATIONS SHOULD BE DONE AT THIS POINT.
         * PREPARE THE PDF GENERATOR
         */
 /*       
        $dompdf = "";
        try {
            $dompdf = PDFGenerator::generate("Report" , $html, $head, false);
            //exit;
        }
        catch(DOMPDF_Internal_Exception $e){
            echo "ERROR!!!";
            echo $e->getMessage();
            // TODO: Display a nice message to the user if the generation failed
        }
        //$pdfdata = $dompdf->stream($reportee_name."_Evaluation.pdf");
        $dompdf = $dompdf->output();
        $data = "";
        $person = Person::newFromId($reporteeId);
        $sto = new ReportStorage($person);
        $sto->store_report($data, $dompdf, 0, 0, RPTP_EVALUATOR_NI);
		$tok = $sto->metadata('token');
        $tst = $sto->metadata('timestamp');
        $len = $sto->metadata('pdf_len');
        echo json_encode(array('tok'=>$tok, 'time'=>$tst, 'len'=>$len));
        
        exit;
    }
    
    // Generates the Full PDF of this report, using dompdf.  
    // Loops through all the sections of the report, and puts together a string of html
    // to be used as input for dompdf
    function generatePDF($pdfType=RPTP_NORMAL){
        global $reportList, $wgOut, $reporteeId;
        $head = <<<EOF
EOF;
        $wgOut->clearHTML();
        for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
            $page = $reportList[$i][0];
            if($page == "archive" || $page == "ni_submit" || $page == "hqp_submit"){
                // Don't display the archived pdfs page
                continue;
            }
            $_POST[$page] = true;
            $wgOut->addHTML("<center><h1>{$reportList[$i][1]}</h1></center>");
            $this->execute("noPDF");
            unset($_POST[$page]);
            $wgOut->addHTML("<div style='page-break-after:always;'></div>");
        }
        
        /*
         * INITIAL CLEANUP
         */
/*          
        $html = str_get_html($wgOut->getHTML(), true, true, DEFAULT_TARGET_CHARSET, false); // Create the dom object
        foreach($html->find('.pdfnodisplay') as $nodisplay){
            $nodisplay->innertext = "";
        }
        foreach($html->find('script') as $s){
            $s->outertext = "";
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
        foreach($html->find("#instructions") as $inst){
            $inst->outertext = '';
        }
        $html->find("[class=basic_info]", 0)->style = 'margin-bottom:0;';
        foreach($html->find('[class=pdfh2]') as $pdfh2){
            $pdfh2->tag = 'h2';
            $pdfh2->outertext = $pdfh2->outertext;
        }
        foreach($html->find('[class=pdfproject_head]') as $p_head){
            $p_head->tag = 'b';
            $p_head->style = 'font-size:14px;';
            //$p_head->outertext = '';
            //$p_head->outertext = '<span style="font-size:14px;">'.$p_head->outertext.'</span>';
            //$p_head->style = 'font-size:14px;';
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
        /*
         * DASHBOARD MANIPULATION
         */
/*
        //Remove all 'Save' buttons
        foreach( $html->find("input[type=submit]") as $submit ){
            $submit->outertext = "";
        } 
        //cleanup hrefs leftover from converting a to b
        foreach( $html->find("b") as $b){
            $b->href = null;
            $b->onclick = null;
        }   
       
       foreach ($html->find("input[class=short]") as $txt){
            $txt->outertext = ($txt->value)? $txt->value : 0;
            $txt->outertext = "<b style='margin-left:5px;'>".$txt->outertext."</b>";
            $parent = $txt->parent();
            $parent->innertext = $parent->last_child()->outertext;
            $parent->innertext .= "<b style='margin-left:10px;'>:</b> ".$txt->outertext;
        }
        
        if( $involved_since = $html->find("input#involved_since", 0)){
            $val = $involved_since->class;
            $involved_since->outertext = "<b>".$val."</b>";
        }
        if($involved_until = $html->find("input#involved_until", 0)){
            $val = $involved_until->value;
            $involved_until->outertext = "<b>".$val."</b>";
        }
        
        foreach ($html->find("[class=pdf_hide]") as $el){
            $el->outertext = "";
        }
        foreach ($html->find("[class=pdf_show]") as $el){
            $el->style = "display:block;";
        }
        
        foreach ($html->find(".milestones_project_hdr") as $hdr){
            $hdr->find("b", 0)->style = "font-size:14px; padding-right:25px;";
            $hdr->find("span", 0)->style = "font-size:14px;";
        }
        
        $html->find("table.milestones_hdr_table",0)->outertext = "";
        
        foreach ( $html->find("table.milestones") as $tbl ){
            foreach ($tbl->find("tr") as $tr ){
                $full_description = "";
                if($worked = $tr->find("td", 0)->find("input[checked=checked]", 0)){
                    $name = $worked->name;
                    $worked = $worked->value;
                    $name = substr($name, 9);  // remove 'projects[' from projects[MEOW][178][contribution]
                    $name = preg_split('/\]\[/', $name, 3);
                    $proj_name = $name[0];
                    $milestone_id = $name[1];
                    $milestone = Milestone::newFromId($milestone_id);
                    $full_description = $milestone->getDescription();
                    //echo "Milestone: $milestone_id:  $full_description<br>";
                }else{
                    $worked = "No";
                }
                
                $descr = "";
                $comment = "";
                if($tr->find("td", 1)){
                    if($tr->find("td", 1)->find("b",0)){
                        $descr = $tr->find("td", 1)->find("b",0)->innertext;
                    }
                }
                if($tr->find("td", 2)){
                    $comment = trim($tr->find("td", 2)->plaintext);
                }
                
                if($comment){
                    $tr->tag = "div";
                    $tr->style = "border-bottom: 1px solid #000; padding: 10px 0;";
                    $tr->innertext =<<<EOD
                        <b>Worked On? </b>$worked<br />
                        <b>Milestone:</b><br />$full_description<br />
                        <b>Comments:</b><br />$comment
EOD;
                }
                else{
                    $tr->outertext = "";
                }
            }
            $tbl->outertext = $tbl->innertext;
        }
        
        
        foreach($html->find(".nce_section") as $nce){
            $nce_id = $nce->id;
            $nce_hdr_id = 'lnk_'.substr($nce_id, 4);
            $nce->find(".section_char_count", 0)->style = "";
            $char_span = $nce->find(".section_char_count", 0)->outertext;
            $nce->find(".section_char_count", 0)->outertext = "";
            $html->find("#{$nce_hdr_id}", 0)->outertext = $html->find("#{$nce_hdr_id}", 0)->outertext." ".$char_span;
            //$nce->outertext = $char_span."<br />".$nce->outertext;
        }
        
        foreach($html->find(".project_header") as $hdr){
            $hdr->outertext = "<span style='font-style:italic;'>(".str_replace(array('&nbsp;',' '),'',$hdr->innertext).")</span> ";
        }
        
        /*
         * BUDGET MANIPULATION
         */

/*
        $budget_just = $html->find('[id=div_budget_just]', 0);
        $budget_just2 = $html2->find('[id=div_budget_just]', 0);
        if($budget_just != null){
            $budget_just->outertext = '';
            $budget_just2->class = '';
            $budget_just2->find('p', 0)->outertext = '';
        }
        $budget = $html->find('[id=budget]', 0);
        if($budget != null){
            $budget->cellpadding = '1';
            $budget->cellspacing = '1';
            $budget->rules = null;
            $budget->boxes = null;
            $budget->frame = null;
            $budget->style = 'background-color:#000000;margin-bottom:15px;';
            $budget->width = '100%';
            foreach($budget->find('td') as $td){
                $td->nowrap = null;
                $td->style = 'background-color:#FFFFFF;';
                $td->colspan = '1';
                if($td->find('b', 0) != null){
                    $td->innertext = "<small>".$td->plaintext."</small>";
                }
            }
            $budget->outertext = $budget->outertext.$budget_just2->innertext;
        }
        else if($budget_just != null){
            $budget_just->outertext = $budget_just2->outertext;
        }

        /*
         * ALL MANIPULATIONS SHOULD BE DONE AT THIS POINT.
         * PREPARE THE PDF GENERATOR
         */
/*        
        $newpdf = "";
        try {
            $newpdf = PDFGenerator::generate("Report" , $html, $head, false);
            //exit;
        }
        catch(DOMPDF_Internal_Exception $e){
            echo "ERROR!!!";
            echo $e->getMessage();
            // TODO: Display a nice message to the user if the generation failed
        }
        
        $newpdf = $newpdf->output();
        $data = "";
        $person = Person::newFromId($reporteeId);
        $sto = new ReportStorage($person);
        $sto->store_report($data, $newpdf, 0, 0, $pdfType);
		$tok = $sto->metadata('token');
        $tst = $sto->metadata('timestamp');
        $len = $sto->metadata('pdf_len');
        echo json_encode(array('tok'=>$tok, 'time'=>$tst, 'len'=>$len));
        exit;
    }
    
    static function printPreviousNext(){
	    global $wgOut, $reportList;
	    
	    $isOnFirst = OldReport::isOnFirst();
	    $isOnLast = OldReport::isOnLast();
	    $wgOut->addHTML("<div class='workflow_nav'>");
	    
	    $list = array();	    
	    for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
	        $current_section = $reportList[$i][0];
	        
	        if( isset($_POST[$current_section]) || ($isOnFirst && $i == REPORT_MIN) || ($isOnLast && $i == REPORT_MAX)){
	            $list[] = "<b style='color:#008800;'>".$reportList[$i][1]. "</b>";
	        }
	        else{
	            $list[] = "<a href='javascript:submit(\"".$reportList[$i][0]."\")'>".$reportList[$i][1]."</a>";
	        }
	    }
	    
	    $wgOut->addHTML(implode(" | ", $list));
      $wgOut->addHTML("</div>");
	}
	
	static function getCurrentElement(){
	    global $reportList;
	    for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
	        $current_section = $reportList[$i][0];
	        if( isset($_POST[$current_section]) ){
	            return $i;
	        }
	    }
	    return REPORT_MIN;
	}
	
	static function isOnFirst(){
	    global $reportList;
	    for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
	        $current_section = $reportList[$i][0];
	        if( isset($_POST[$current_section]) && $i != REPORT_MIN){
	            return false;
	        }
	    }
	    return true;
	}
	
	static function isOnLast(){
	    global $reportList;
	    for($i = REPORT_MIN; $i <= REPORT_MAX; $i++){
	        $current_section = $reportList[$i][0];
	        if( isset($_POST[$current_section]) && $i == REPORT_MAX){
	            return true;
	        }
	    }
	    return false;
	}
}

class OldReport extends AbstractReportOld{

	function OldReport(){
	    parent::AbstractReportOld("OldReport", HQP.'+');
	}
	
	function initReport(){
      global $reportList, $wgTitle, $wgUser, $wgServer, $wgScriptPath, $wgOut, $viewOnly, $reporteeId, $pniAdmin;
      //$viewOnly = true; //True when viewing the report on somebodys behalf, as determined below
      $pniAdmin = false;
      $page = "$wgServer$wgScriptPath/index.php/Special:Report";
      
      $uid = $wgUser->getId();
      $reporteeId = $uid;
		$person = Person::newFromId($uid);
		$autosave = "autosave";
		
		//Are they allowed and do they want to access PNI-Admin Budget report?
		if ($person->isRole(PNIA) && (isset($_GET['pni_admin']) || isset($_POST['pni_admin'])) ){
		    $pniAdmin = true;
		}    
		
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
        else if(($person->isRole(CNI) || $person->isRole(PNI)) && isset($_GET['generatePDF'])){
            $onbehalf = (isset($_GET['person']))? Person::newFromName($_GET['person']) : NULL;
            if(!is_null($onbehalf)){
                $person = $onbehalf;
                $sups = $person->getSupervisors();
                
                foreach($sups as $sup){
                    if($sup->getId() == $uid){
                        $reporteeId = $onbehalf->getId();
                        $viewOnly = true;
                        $autosave = "";
                    }
                }
            }
        }      
        else if($person->isRole(RMC)){
            $onbehalf = (isset($_GET['person']))? Person::newFromName($_GET['person']) : NULL;
            if(!is_null($onbehalf)){
                foreach($person->getEvaluateSubs() as $sub){
                    if($sub instanceof Person){
                        if($sub->getName() == $onbehalf->getName()){
                            $person = $onbehalf;
                            $reporteeId = $onbehalf->getId();
                            $viewOnly = true;
                            $autosave = "";
                            break;
                        }
                    }
                }
            }
        }
		//$autosave = "";
		
		$viewOnly = ( $viewOnly || FROZEN );
		if($viewOnly){
		    $autosave = "";
		}
		
		if(!defined("REPORT_MIN")){
		    define("REPORT_MIN", 0);
		}
		
		if( $person->isHQP() ){
            $reportList[0] = array( "hqp_dashboard", "HQP Dashboard", array("Dashboard", "HQPDashboard"), "" );
            $reportList[1] = array( "hqp_questionnaire", "HQP Questionnaire", array("Dashboard", "HQPQuestionnaire"), $autosave );
            $reportList[2] = array( "hqp_report", "HQP Report", array("Dashboard", "HQPReport"), $autosave );
            $reportList[3] = array( "ni_submit", "Review & Submit", array("Dashboard", "HQPSubmitReport"), "" );
        }
        else if( ($person->isCNI() || $person->isPNI()) && !$pniAdmin ){
            $reportList[0] = array( "ni_dashboard", "NI Dashboard", array("Dashboard", "NIDashboard"), "" );
            $reportList[1] = array( "ni_questionnaire", "NI Questionnaire", array("Dashboard", "NIQuestionnaire"), $autosave );
            $reportList[2] = array( "ni_report", "NI Report", array("Dashboard", "NIReport"), $autosave );
            $reportList[3] = array( "ni_budget", "NI Budget", array("Dashboard", "NIBudget"), $autosave );
            $reportList[4] = array( "ni_submit", "Review & Submit", array("Dashboard", "NISubmitReport"), "" );
        }
        else if ($pniAdmin){
            $reportList[0] = array( "pni_admin_budget", "PNI Admin Budget", array("Dashboard", "PNIAdminBudget"), $autosave );
        }
        else{
            $reportList[0] = array( "ni_dashboard", "NI Dashboard", array("Dashboard", "NIDashboard"), "" );
        }
        
        if(!defined("REPORT_MAX")){
            define("REPORT_MAX", count($reportList)-1);
        }
        
        $current_page = OldReport::getCurrentElement();
        $class = "class='{$reportList[$current_page][3]}'";
        $saveAll = "";
        if($reportList[$current_page][3] == "autosave"){
            $saveAll = "saveAll();";
        }
        
        $get_param = "";
		if( $viewOnly ){
		    $get_param = "?person=".$person->getName();
		    
		    //$wgOut->addHTML("<p style='color:red;font-style:italic;'>* Currently viewing the report for {$person->getName()}</p>");
		    $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        $('#contentSub').attr('style', 'margin-left:0px;');
                                        $('#contentSub').html(
                                        '<p style=\"color:red;font-style:italic;font-size:12px; \">* Currently viewing the report for {$person->getName()}</p>');
                                    });
                                </script>");
		}
		else{
		    $wgOut->addScript("<script type='text/javascript'>
                                    $(document).ready(function(){
                                        $('#loadingDiv').hide();  // hide it initially
                                        $('#contentSub').attr('style', 'margin-left:0px;');
                                        $('#contentSub').html(
                                        '<a style=\"display:inline-block; font-size: 12px; font-weight: bold; margin-top: 7px;\" href=\"/index.php/GRAND:Reporting_2011_Instructions\" target=\"_blank\">2011 Reporting-Process Overview</a><p style=\"font-size:14px; color:red;\">Important: Please do not log-in on this site in multiple browsers to prevent potential data loss due to auto-saving functionality.</p>');
                                    });
                                </script>");
		}
        
        $wgOut->addScript("<script src='../scripts/jquery.limit-1.2.source.js' type='text/javascript' charset='utf-8';></script>");
        $wgOut->addScript("<script type='text/javascript'>
		                    function submit(i){
		                        $saveAll 
		                        var input = document.createElement('input');
		                        input.type = 'hidden';
		                        input.name = i;
		                        input.value = i;
		                        document.report.appendChild(input);
                                $('#ni_submit').remove();
                                $('#action_type').remove();
		                        document.report.submit();
		                     }
		                     
		                     function submitReportAction(action){
		                         var input = document.createElement('input');
 		                         input.type = 'hidden';
 		                         input.id = 'ni_submit';
 		                         input.name = 'ni_submit';
 		                         input.value = 'ni_submit';
 		                         document.report.appendChild(input);
 		                         
 		                         $('#action_type').val(action);
 		                         document.report.submit();
		                     }".
		                     <<<EOF
		                     function generateHQPReport(name, id){
		                        $('#loadingDiv').show();
		                        $.ajax({
                                   url: '$page?person=' + name + '&generatePDF',
                                   context: document.body,
                                   success: function(data){
                                       $('#loadingDiv').hide();
                                       var data = jQuery.parseJSON(data);
                                       $('#tok' + id).attr('href', '$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=' + data.tok);
                                       $('#tst' + id).html(data.time);
                                       $('#hqp' + id).attr('style', 'display:block');
                                   }
                                 });
		                     }
		                     
		                     function generateReport(){
		                         $('#loadingDiv').show();
		                         $.ajax({
                                   url: '$page?generatePDF',
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
		                     }
EOF
				            ."</script>");
				            
		$wgOut->addStyle("cavendish/reports.css", "screen");
        $wgOut->addHTML("<form $class action='$wgServer$wgScriptPath/index.php/Special:{$this->reportName}{$get_param}' method='post' name='report' enctype='multipart/form-data'>");
    }

	function run(){
		global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $reportList, $viewOnly, $reporteeId;
		$uid = $reporteeId; //$wgUser->getId();
		$person = Person::newFromId($uid);
		
		//$wgOut->addHTML("<center><font style='font-size:14px;'><b>".WORKFLOW_NAME."</b></font></center><br />");
		$instructions_html = '<div id="loadingDiv" style="position:absolute;"><img width="16" height="16" src="../skins/Throbber.gif" />Please wait while the report is generated.</div><div id="instructions"><img style="display:none;" width="16" height="16" src="../skins/Throbber.gif" />';
		$curr_element = $reportList[OldReport::getCurrentElement()][0];
		if( ($person->isHQP() && OldReport::getCurrentElement() >= 0) || (($person->isCNI() || $person->isPNI()) && OldReport::getCurrentElement() > 0 && OldReport::getCurrentElement() < REPORT_MAX)){
		    $instructions_path = "$wgServer$wgScriptPath/extensions/Report/Instructions/".$curr_element.".html";
		    $instructions_html .=<<<EOF
		    <input class="report_button" type="button" name="instr" value="Instructions" 
		    onClick='window.open("$instructions_path", "", "width=800,height=600,scrollbars=yes");' /> 
EOF;
        }
        
        if( !$viewOnly && (($person->isHQP() && OldReport::getCurrentElement() > 0) || (($person->isCNI() || $person->isPNI()) && OldReport::getCurrentElement() > 0 && OldReport::getCurrentElement() < REPORT_MAX)) ){
            $instructions_html .=<<<EOF
		    <input class="report_button" type="submit" name="$curr_element" value="Save" /><br />
EOF;
        }

		$wgOut->addHTML($instructions_html."</div>");
        
        call_user_func( $reportList[OldReport::getCurrentElement()][2] );
        if( !$viewOnly && (($person->isHQP() && OldReport::getCurrentElement() > 0) || (($person->isCNI() || $person->isPNI()) && OldReport::getCurrentElement() > 1 && OldReport::getCurrentElement() < REPORT_MAX)) ){
		    $wgOut->addHTML( "<br /><br /><input class=\"report_button\" type=\"submit\" name=\"$curr_element\" value=\"Save\" />");
        } 
	}
}
*/
?>
