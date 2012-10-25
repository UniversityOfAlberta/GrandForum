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

	   /* $sql =<<<EOF
SELECT r.user, u.user_name, u.user_email, uu.position, r.end_date 
FROM grand_roles r 
INNER JOIN mw_user u ON ( u.user_id = r.user )
LEFT JOIN grand_movedOn mo ON ( r.user = mo.user_id ) 
LEFT join mw_user_university uu ON (uu.user_id = r.user)
WHERE r.role="HQP" AND YEAR(r.end_date)="2011" AND mo.user_id IS NULL
EOF;

		echo "user,user_name,user_email,position,supervisor_name,supervisor_email,end_date<br />";
		$data = DBFunctions::execSQL($sql);
		foreach($data as $row){
			$pid = $row['user'];

			$pers = Person::newFromId($pid);
			if(!$pers->isActive()){

				$sql2 =<<<EOF
SELECT u.user_name AS supervisor_name, u.user_email AS supervisor_email 
FROM grand_relations r 
INNER JOIN mw_user u ON ( u.user_id = r.user1 )
WHERE r.type='Supervises' 
AND r.user2={$pid} 
ORDER BY r.id DESC LIMIT 1
EOF;
				$data2 = DBFunctions::execSQL($sql2);
				if(isset($data2[0])){
					$row2=$data2[0];

					echo "{$row['user']},{$row['user_name']},{$row['user_email']},{$row['position']},{$row2['supervisor_name']},{$row2['supervisor_email']},{$row['end_date']}<br />";
				
				}
			}

		}

	    */
		if(isset($_GET['section']) && $_GET['section'] == 'NSERC'){
		    $tabbedPage = new TabbedPage("tabs_nserc");
		    //if(isset($_GET['year']) && $_GET['year'] == '2011'){
		    	$tabbedPage->addTab(new NSERC2013Tab());
		    	$tabbedPage->addTab(new NSERC2012Tab());
		    	$tabbedPage->addTab(new NSERC2011Tab());
			//}else{
				
			//}
	        $tabbedPage->showPage();
    	}
    	else{
    		$tabbedPage = new TabbedPage("tabs_rmc");
    		//if(isset($_GET['year']) && $_GET['year'] == '2011'){
    			$tabbedPage->addTab(new RMC2013Tab());
		    	$tabbedPage->addTab(new RMC2012Tab());
		    	$tabbedPage->addTab(new RMC2011Tab());
			//}else{
				
			//}
	        $tabbedPage->showPage();
    	}
    	
	}
} 
