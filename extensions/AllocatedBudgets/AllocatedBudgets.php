<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['SkinTemplateContentActions'][] = 'AllocatedBudgets::showTabs';

$wgSpecialPages['AllocatedBudgets'] = 'AllocatedBudgets';
$wgExtensionMessagesFiles['AllocatedBudgets'] = $dir . 'AllocatedBudgets.i18n.php';
$wgSpecialPageGroups['AllocatedBudgets'] = 'reporting-tools';

function runAllocatedBudgets($par) {
	AllocatedBudgets::run($par);
}

class AllocatedBudgets extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('AllocatedBudgets');
		SpecialPage::SpecialPage("AllocatedBudgets", MANAGER.'+', true, 'runAllocatedBudgets');
	}
	
	function run(){
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
	    $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='none'>
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
	            if($wgRoleValues[$role->getRole()] >= $wgRoleValues[CNI]){
	                $found = true;
	                break;
	            }
	        }
	        if($found){
	            //$data = AllocatedBudgets::getData($year, $person);
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
    
    static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        $current_selection = (isset($_GET['year']) && is_numeric($_GET['year'])) ? $_GET['year'] : date('Y')-1;
        
        if($wgTitle->getText() == "AllocatedBudgets"){
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
                if($current_selection == $i){
                    $class = "selected";
                }
                else{
                    $class = false;
                }
                $content_actions[] = array (
                     'class' => $class,
                     'text'  => $i,
                     'href'  => "$wgServer$wgScriptPath/index.php/Special:AllocatedBudgets?year={$i}",
                    );
            }
        }
        return true;
    }
}

?>
