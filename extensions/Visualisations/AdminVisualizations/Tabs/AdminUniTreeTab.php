<?php

$wgHooks['UnknownAction'][] = 'AdminUniTreeTab::getAdminUniTreeData';

class AdminUniTreeTab extends AbstractTab {
	
	function AdminUniTreeTab(){
        parent::AbstractTab("University Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    for($year=2011; $year <= REPORTING_YEAR+1; $year++){
	        $this->html .= "<h2>$year</h2>";
	        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getAdminUniTreeData&date={$year}");
	        $tree->height = 500;
	        $tree->width = 1000;
	        $this->html .= $tree->show();
	        $this->html .= "<script type='text/javascript'>
                $('#adminVis').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == 'university-funding'){
                        onLoad{$tree->index}();
                    }
                });
                </script><br />";
	    }
	}
	
	static function getAdminUniTreeData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : REPORTING_YEAR;
	    if($action == "getAdminUniTreeData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();  
            $data = array("name" => "GRAND",
                          "children" => array());
            $people = Person::getAllPeopleDuring(null, $year."-01-01", $year."-12-31");
            $unis = array();
            foreach($people as $person){
                if($person->isRoleDuring(CNI, $year."-01-01", $year."-12-31") ||
                   $person->isRoleDuring(PNI, $year."-01-01", $year."-12-31")){
                    $uni = $person->getUniversityDuring($year."01-01", $year."12-31");
                    if($uni['university'] == ""){
                        $uni['university'] = "Unknown";
                    }
                    $budget = $person->getRequestedBudget($year-1);
                    if($budget != null){
                        $total = str_replace('$', "", $budget->copy()->rasterize()->where(HEAD1, array("TOTALS%"))->limit(0, 1)->select(ROW_TOTAL)->toString());
                        @$unis[$uni['university']][$person->getName()] = ($total == "") ? "0" : $total;
                    }
                }
            }
            foreach($unis as $uni => $person){
                $university = University::newFromName($uni);
                if($uni == "Unknown"){
                    $color = "#888888";
                }
                else{
                    $color = $university->getColor();
                }
                $uniData = array("name" => $uni,
                                 "color" => $color,
                                 "children" => array());
                $personData = array();
                foreach($person as $name => $total){
                    $personData[] = array("name" => $name,
                                          "size" => $total);
                }
                $uniData['children'] = $personData;
                $data['children'][] = $uniData;
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
