<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['SubLevelTabs'][] = 'AllocatedBudgets::createSubTabs';

$wgSpecialPages['AllocatedBudgets'] = 'AllocatedBudgets';
$wgExtensionMessagesFiles['AllocatedBudgets'] = $dir . 'AllocatedBudgets.i18n.php';
$wgSpecialPageGroups['AllocatedBudgets'] = 'reporting-tools';

function runAllocatedBudgets($par) {
	AllocatedBudgets::execute($par);
}

class AllocatedBudgets extends SpecialPage {

	function __construct() {
		SpecialPage::__construct("AllocatedBudgets", MANAGER.'+', true, 'runAllocatedBudgets');
	}
	
	function execute(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgRoleValues;
	    $wgOut->addScript("<script type='text/javascript'>
            $(document).ready(function(){
                $('.indexTable').css('display', 'table');
                $('.dataTables_filter').css('float', 'none');
                $('.dataTables_filter').css('text-align', 'left');
                $('.dataTables_filter input').css('width', 250);
            });
        </script>");
	    if(date('m') >= 3){
            $year = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y');
        }
        else{
            $year = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y') - 1;
        }
        if(isset($_GET['download']) && isset($_GET['person'])){
            $person = Person::newFromName($_GET['person']);
	        $data = AllocatedBudgets::getData($year, $person);
	        header('Content-Type: application/vnd.ms-excel');
	        header("Content-disposition: attachment; filename='{$person->getNameForForms()}_{$year}_AllocatedBudget.xls'");
	        echo $data;
            exit;
        }
	    $people = Person::getAllPeopleDuring('all', "{$year}-00-00", ($year+1)."-00-00");
	    $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th>Name</th><th>Allocated Budget</th><th>Total</th>
                                </tr>
                            </thead>
                            <tbody>");
	    foreach($people as $person){
	        $found = false;
	        $roles = $person->getRolesDuring("{$year}-00-00", ($year+1)."-00-00");
	        foreach($roles as $role){
	            if($wgRoleValues[$role->getRole()] >= $wgRoleValues[NI]){
	                $found = true;
	                break;
	            }
	        }
	        if($found){
	            $budget = $person->getAllocatedBudget($year-1);
	            if($budget != null && $budget->size() > 0){
	                $download = "<a href='$wgServer$wgScriptPath/index.php/Special:AllocatedBudgets?year={$year}&person={$person->getName()}&download'>Download Budget</a>";
	                $budget = $budget->copy()->filterCols(V_PROJ, array(""));
	                
	                $projectTotals = $budget->copy()->rasterize()->where(HEAD1, array("TOTALS.*"));
	                $total = $projectTotals->copy()->select(ROW_TOTAL)->sum();
	                if($budget->isError()){
	                    $msg = "";
	                    foreach($budget->xls as $rowN => $row){
				            foreach($row as $colN => $cell){
				                if($cell->error != ""){
					                $msg .= str_replace("'", "&#39;", "-{$cell->error}<br />");
					            }
					        }
				        }
	                    $errors = "<span style='float:left;font-weight:bold;color:#FF0000;' title='$msg' class='tooltip'>ERRORS</span> $".number_format(str_replace("$", "", $total->toString()));
	                }
	                else{
	                    if($total->toString() == "$"){
	                        $errors = "Not Uploaded";
	                        $download = "";
	                    }
	                    else{
	                        $errors = "$".@number_format(str_replace("$", "", $total->toString()));
	                    }
	                }
	            }
	            else{
	                $download = "";
	                $errors = "Not Uploaded";
	            }
	            
	            $splitName = $person->splitName();
	            $wgOut->addHTML("<tr>
	                                <td><a target='_blank' href='{$person->getUrl()}'>{$person->getReversedName()}</a></td><td>$download</td><td align='right'>{$errors}</td>
	                             </tr>");
	        }
	    }
	    $wgOut->addHTML("</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100});</script>");
    }
    
    static function getData($year, $person){
        $uid = $person->getId();
        $blob_type=BLOB_EXCEL;
        $rptype = RP_RESEARCHER;
    	$section = RES_ALLOC_BUDGET;
    	$item = 0;
    	$subitem = 0;
        $rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
        $budget_blob = new ReportBlob($blob_type, ($year-1), $uid, 0);
        $budget_blob->load($rep_addr);
        $data = $budget_blob->getData();
        return $data;
    }
    
    static function createSubTabs(&$tabs){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if($wgTitle->getText() == "AllocatedBudgets"){
            $current_selection = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y')-1;
            $content_actions = array();
            
            $year = "2011";
            for($i = date('Y'); $i >= $year; $i--){
                if($i == date('Y')){
                    if(date('m') >= 3){
                        $current_selection = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y');
                    }
                    else{
                        continue;
                    }
                }
                $selected = ($current_selection == $i) ? "selected" : false;
                $tabs['Other']['subtabs'][] = TabUtils::createSubTab($i, "$wgServer$wgScriptPath/index.php/Special:AllocatedBudgets?year={$i}", $selected);
            }
        }
        return true;
    }
}

?>
