<?php

autoload_register('ReportTables');

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['EvaluationTable'] = 'EvaluationTable';
$wgExtensionMessagesFiles['EvaluationTable'] = $dir . 'EvaluationTable.i18n.php';
$wgSpecialPageGroups['EvaluationTable'] = 'report-reviewing';

$foldscript = "
<script type='text/javascript'>
function mySelect(form){ form.select(); }
function ShowOrHide(d1, d2) {
	if (d1 != '') DoDiv(d1);
	if (d2 != '') DoDiv(d2);
}
function DoDiv(id) {
	var item = null;
	if (document.getElementById) {
		item = document.getElementById(id);
	} else if (document.all) {
		item = document.all[id];
	} else if (document.layers) {
		item = document.layers[id];
	}
	if (!item) {
	}
	else if (item.style) {
		if (item.style.display == 'none') { item.style.display = ''; }
		else { item.style.display = 'none'; }
	}
	else { item.visibility = 'show'; }
}
function showdiv(div_id, details_div_id){   
    details_div_id = '#' + details_div_id;
    $(details_div_id).html( $(div_id).html() );
    $(details_div_id).show();
}
</script>
<style media='screen,projection' type='text/css'>
#details_div, .details_div{
    border: 1px solid #CCCCCC;
    margin-top: 10px;
    padding: 10px;
    position: relative;
    width: 980px;
} 
</style>
";

function runEvaluationTable($par) {
	global $wgScriptPath, $wgOut, $wgUser, $wgTitle, $_tokusers;
	EvaluationTable::show();
	//$wgOut->setPageTitle("Evaluation Table 2011");
}

// Ideally these would be inside the class and be used.
$_pdata;
$_pdata_loaded = false;
$_projects;

class EvaluationTable extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('EvaluationTable');
		SpecialPage::SpecialPage("EvaluationTable", MANAGER.'+', true, 'runEvaluationTable');
	}
	
	static function show(){
		require_once('RMC2013Tab.php');
        require_once('NSERC2013Tab.php');
	    require_once('RMC2012Tab.php');
        require_once('NSERC2012Tab.php');
        require_once('RMC2011Tab.php');
        require_once('NSERC2011Tab.php');
        require_once('Nominations.php');
        require_once('Productivity.php');
        require_once('ResearcherProductivity.php');
        require_once('Themes.php');
        require_once('EvaluatorIndex.php');
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript;
	 
	    $init_tabs = array('2013'=>0, '2012'=>1, '2011'=>2);
	    $init_tab = 0;
	    if(isset($_GET['year'])){
	    	$init_tab = $init_tabs[$_GET['year']];
	    }
		if(isset($_GET['section']) && $_GET['section'] == 'NSERC'){
		    $tabbedPage = new TabbedPage("tabs_nserc");
		    //if(isset($_GET['year']) && $_GET['year'] == '2011'){
		    	$tabbedPage->addTab(new NSERC2013Tab());
		    	$tabbedPage->addTab(new NSERC2012Tab());
		    	$tabbedPage->addTab(new NSERC2011Tab());
			//}else{
				
			//}

	        $tabbedPage->showPage($init_tab);
    	}
    	else{
    		$tabbedPage = new TabbedPage("tabs_rmc");
    		//if(isset($_GET['year']) && $_GET['year'] == '2011'){
    			$tabbedPage->addTab(new RMC2013Tab());
		    	$tabbedPage->addTab(new RMC2012Tab());
		    	$tabbedPage->addTab(new RMC2011Tab());
			//}else{
				
			//}
	        $tabbedPage->showPage($init_tab);
    	}
    	
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
        return "<a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?getpdf={$tok}'>[Download&nbspPDF]</a>";
	}
} 
