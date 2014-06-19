<?php

$wgHooks['UnknownAction'][] = 'AdminProjTreeTab::getAdminProjTreeData';

class AdminProjTreeTab extends AbstractTab {
	
	function AdminProjTreeTab(){
        parent::AbstractTab("Project Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    for($year=2011; $year <= REPORTING_YEAR+1; $year++){
	        $this->html .= "<h2>$year</h2>";
	        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getAdminProjTreeData&date={$year}", "Funding", "Count");
	        $tree->height = 500;
	        $tree->width = 1000;
	        $this->html .= $tree->show();
	        $this->html .= "<script type='text/javascript'>
                $('#adminVis').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == 'project-funding'){
                        onLoad{$tree->index}();
                    }
                });
                </script><br />";
	    }
	}
	
	static function getAdminProjTreeData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : REPORTING_YEAR;
	    if($action == "getAdminProjTreeData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();  
            $data = array("name" => "GRAND",
                          "children" => array());
            $projs = array();
            $projects = Project::getAllProjectsDuring($year."-01-01", $year."-12-31");
            $people = Person::getAllPeopleDuring(null, $year."-01-01", $year."-12-31");
            foreach($projects as $project){
                $budget = $project->getRequestedBudget($year-1);
                if($budget != null){
                    $people = $budget->copy()->where(V_PERS_NOT_NULL)->select(V_PERS_NOT_NULL);
                    if($people->nRows() > 0){
                        foreach($people->xls[0] as $cell){
                            $name = $cell->getValue();
                            $total = str_replace('$', "", $budget->copy()->rasterize()->select(V_PERS_NOT_NULL, array($name))->where(CUBE_COL_TOTAL)->toString());
                            @$projs[$project->getName()][$name] = ($total == "") ? "0" : $total;
                        }
                    }
                }
            }
            foreach($projs as $proj => $person){
                $project = Project::newFromName($proj);
                $challenge = $project->getChallenge();
                $theme = ($challenge != null) ? $challenge->getAcronym() : "Unknown";
                switch($theme){
                    case "nMEDIA": 
                        $color = "#B6D661";
                        break;
                    case "GamSim":
                        $color = "#CF292D";
                        break;
                    case "AnImage":
                        $color = "#FCB722";
                        break;
                    case "SocLeg":
                        $color = "#23A69D";
                        break;
                    case "TechMeth":
                        $color = "#8D539F";
                        break;
                    case "Big Data":
                        $color = "#21A3DC";
                        break;
                    case "Citizenship":
                        $color = "#FFC90D";
                        break;
                    case "Entertainment":
                        $color = "#723C96";
                        break;
                    case "Healthcare":
                        $color = "#EC2528";
                        break;
                    case "Learning":
                        $color = "#F47F20";
                        break;
                    case "Sustainability":
                        $color = "#12A551";
                        break;
                    case "Work":
                        $color = "#075693";
                        break;
                    default:
                        $color = "#888888";
                        break;
                }
                $projData = array("name" => $proj,
                                  "color" => $color,
                                  "children" => array());
                $personData = array();
                foreach($person as $name => $total){
                    $personData[] = array("name" => $name,
                                          "size" => $total);
                }
                $projData['children'] = $personData;
                $data['children'][] = $projData;
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
