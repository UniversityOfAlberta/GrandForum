<?php

UnknownAction::createAction('AdminProjTreeTab::getAdminProjTreeData');

class AdminProjTreeTab extends AbstractTab {
	
	function AdminProjTreeTab(){
        parent::AbstractTab("Project Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $this->html .= "The following tree map visualizations show the distribution of funding for Themes/Projects/People.  Clicking on a section will zoom in to that section.  If 'Funding' is selected, the area that the section takes up is based on how much funding each section gets.  If 'Count' is selected, the area is based on how many sub-sections each section has.";
	    for($year=2011; $year <= REPORTING_YEAR+1; $year++){
	        $this->html .= "<h2>$year</h2>";
	        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getAdminProjTreeData&date={$year}", "Funding", "Count", "$", "");
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
                            $challenge = $project->getChallenge();
                            $theme = ($challenge != null) ? $challenge->getAcronym() : "Unknown";
                            @$projs[$theme][$project->getName()][$name] = ($total == "") ? "0" : $total;
                        }
                    }
                }
            }
            foreach($projs as $theme => $projs2){
                $challenge = Theme::newFromName($theme);
                $color = $challenge->getColor();
                $themeData = array("name" => $theme,
                                   "color" => $color,
                                   "children" => array());
                foreach($projs2 as $proj => $person){
                    $project = Project::newFromName($proj);
                    $challenge = $project->getChallenge();
                    $theme = ($challenge != null) ? $challenge->getAcronym() : "Unknown";
                    
                    $projData = array("name" => $proj,
                                      "color" => $color,
                                      "children" => array());
                    $personData = array();
                    foreach($person as $name => $total){
                        $personData[] = array("name" => $name,
                                              "size" => $total);
                    }
                    $projData['children'] = $personData;
                    $themeData['children'][] = $projData;
                }
                $data['children'][] = $themeData;
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
