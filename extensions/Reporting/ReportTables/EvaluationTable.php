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
	    require_once('NSERCTab.php');
		require_once('NSERCVariableTab.php');
		require_once('RMC2014Tab.php');
		require_once('RMC2013Tab.php');
	    require_once('RMC2012Tab.php');
        require_once('NSERC2012Tab.php');
        require_once('RMC2011Tab.php');
        require_once('NSERC2011Tab.php');
        require_once('Nominations.php');
        require_once('Productivity.php');
        require_once('ResearcherProductivity.php');
        require_once('Themes.php');
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $foldscript;
	 
	    $init_tab = 0;
	    
		if(isset($_GET['section']) && $_GET['section'] == 'NSERC'){
			$init_tabs = array('Jan-Dec2012' => 0, 
			                   'Apr2012-Mar2013' => 1, 
			                   'Jan-Mar2012' => 2, 
			                   'Apr-Dec2012' => 3, 
			                   'Jan-Mar2013' => 4, 
			                   '2012' => 5, 
			                   '2011' => 6);

			if(isset($_GET['year']) && isset($init_tabs[$_GET['year']])){
		    	$init_tab = $init_tabs[$_GET['year']];
		    }
		    
		    $tabbedPage = new TabbedPage("tabs_nserc");
		    
		    $tabbedPage->addTab(new NSERCTab(2014));
		    $tabbedPage->addTab(new NSERCTab(2013));

	    	$tabbedPage->addTab(new NSERC2012Tab());
	    	$tabbedPage->addTab(new NSERC2011Tab());
	    	
	        $tabbedPage->showPage($init_tab);
    	}
    	else{
    		$init_tabs = array('2014' => 0,
    		                   '2013' => 1, 
    		                   '2012' => 2, 
    		                   '2011' => 3);

    		if(isset($_GET['year']) && isset($init_tabs[$_GET['year']])){
		    	$init_tab = $init_tabs[$_GET['year']];
		    }
    		
    		$tabbedPage = new TabbedPage("tabs_rmc");
    		
    		$tabbedPage->addTab(new RMC2014Tab());
			$tabbedPage->addTab(new RMC2013Tab());
	    	$tabbedPage->addTab(new RMC2012Tab());
	    	$tabbedPage->addTab(new RMC2011Tab());
		
	        $tabbedPage->showPage($init_tab);
    	}
	}
}

?>
