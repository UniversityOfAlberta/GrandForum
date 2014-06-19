<?php

$wgHooks['UnknownAction'][] = 'AdminDiscTreeTab::getAdminDiscTreeData';

class AdminDiscTreeTab extends AbstractTab {
	
	function AdminDiscTreeTab(){
        parent::AbstractTab("Discipline Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    for($year=2011; $year <= REPORTING_YEAR+1; $year++){
	        $this->html .= "<h2>$year</h2>";
	        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getAdminDiscTreeData&date={$year}", "Funding", "Count");
	        $tree->height = 500;
	        $tree->width = 1000;
	        $this->html .= $tree->show();
	        $this->html .= "<script type='text/javascript'>
                $('#adminVis').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == 'discipline-funding'){
                        onLoad{$tree->index}();
                    }
                });
                </script><br />";
	    }
	}
	
	static function getAdminDiscTreeData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : REPORTING_YEAR;
	    if($action == "getAdminDiscTreeData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();
            $data = array("name" => "GRAND",
                          "children" => array());
            $people = Person::getAllPeopleDuring(null, $year."-01-01", $year."-12-31");
            $unis = array();
            foreach($people as $person){
                if($person->isRoleDuring(CNI, $year."-01-01", $year."-12-31") ||
                   $person->isRoleDuring(PNI, $year."-01-01", $year."-12-31")){
                    $disc = $person->getDisciplineDuring($year."01-01", $year."12-31");
                    $budget = $person->getRequestedBudget($year-1);
                    if($budget != null){
                        $total = str_replace('$', "", $budget->copy()->rasterize()->where(HEAD1, array("TOTALS%"))->limit(0, 1)->select(ROW_TOTAL)->toString());
                        @$unis[$disc][$person->getName()] = ($total == "") ? "0" : $total;
                    }
                }
            }
            foreach($unis as $disc => $person){
                switch($disc){
                    case "Unknown":
                    case "Other":
                        $color = "#888888";
                        break;
                    case "Computer Science";
                        $color = "#d2232a";
                        break;
                    case "Other Sciences & Engineering":
                        $color = "#00a79d";
                        break;
                    case "Media, Arts & Design":
                        $color = "#fdb913";
                        break;
                    case "Other Social Sciences & Humanities":
                        $color = "#8f53a1";
                        break;
                    case "Information Science":
                        $color = "#a6ce39";
                        break;
                }
                $discData = array("name" => $disc,
                                  "color" => $color,
                                  "children" => array());
                $personData = array();
                foreach($person as $name => $total){
                    $personData[] = array("name" => $name,
                                          "size" => $total);
                }
                $discData['children'] = $personData;
                $data['children'][] = $discData;
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
