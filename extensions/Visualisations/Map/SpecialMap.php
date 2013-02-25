<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialMap'] = 'SpecialMap';
$wgExtensionMessagesFiles['SpecialMap'] = $dir . 'SpecialMap.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialMap::getSpecialMapData';

function runSpecialMap($par) {
	SpecialMap::run($par);
}

class SpecialMap extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialMap');
		SpecialPage::SpecialPage("SpecialMap", MANAGER.'+', true, 'runSpecialMap');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $map = new Map("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialMapData");
	    $map->height = "700px";
	    $map->width = "90%";
	    $string = $map->show();
	    
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialMapData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($action == "getSpecialMapData" && $me->isRoleAtLeast(MANAGER)){
	        $uniProvMap = array('Carlton University' => 'CA-ON',
	                            'Concordia University' => 'CA-QC',
	                            'Dalhousie University' => 'CA-NS',
	                            'Ecole de technologie superieure de l universite du Quebec' => 'CA-QC',
	                            'McGill University' => 'CA-QC',
	                            'Nova Scotia College of Art and Design' => 'CA-NS',
	                            'Ontario College of Art & Design' => 'CA-ON',
	                            'Queen`s University' => 'CA-ON',
	                            'Ryerson University' => 'CA-ON',
	                            'Simon Fraser University' => 'CA-BC',
	                            'University of Alberta' => 'CA-AB',
	                            'University of British Columbia' => 'CA-BC',
	                            'University of Calgary' => 'CA-AB',
	                            'University of Manitoba' => 'CA-MB',
	                            'University of Montreal' => 'CA-QC',
	                            'University of Ottawa' => 'CA-ON',
	                            'University of Saskatchewan' => 'CA-SK',
	                            'University of Toronto' => 'CA-ON',
	                            'University of Victoria' => 'CA-BC',
	                            'University of Waterloo' => 'CA-ON',
	                            'University of Western Ontario' => 'CA-ON',
	                            'York University' => 'CA-ON',
	                            'Emily Carr University of Art and Design' => 'CA-BC',
	                            'University of Ontario Institute of Technology' => 'CA-ON',
	                            'Wilfrid Laurier University' => 'CA-ON',
	                            'Royal Rhodes University' => 'CA-BC'); 
	    
	        $array = array();
	        $people = Person::getAllPeople();
	        $pNames = array();
	        $pBudget = array();
	        $rBudget = array();
	        foreach($people as $person){
	            if(!$person->isRoleDuring(PNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH) && 
	               !$person->isRoleDuring(CNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH)){
	                continue;
	            }
                $abudget = $person->getAllocatedBudget(REPORTING_YEAR-1);
                if($abudget != null){
                    $totalBudget = $abudget->copy()->rasterize()->where(COL_TOTAL)->select(ROW_TOTAL);
                    if($totalBudget->nRows() > 0 && $totalBudget->nCols() > 0){
                        $value = $totalBudget->toString();
                        $value = (int)str_replace(',', '', str_replace('$', '', $value));
                        $aTotal = $value;
                    }
                    else{
                        $aTotal = 0;
                    }
                }
                else{
                    $aTotal = 0;
                }
                $university = $person->getUniversity();
                $uni = $university['university'];
                if($uni == ''){
                    $uni = 'Unknown';
                }
                @$pBudget[$uni] += $aTotal;
	        }
	        $i=0;
	        foreach($pBudget as $uni => $total){
	            if(isset($uniProvMap[$uni])){
	                @$array['values'][$uniProvMap[$uni]] += $total;
	                @$array['text'][$uniProvMap[$uni]] .= "<b>$uni:</b> $".number_format($total)."<br />";
	                $i++;
	            }
	        }

            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
	
}
?>
