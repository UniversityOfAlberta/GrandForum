<?php

class AdminProjTreeTab extends AbstractTab {
	
	function __construct(){
        parent::__construct("Project Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
	    $this->html .= "The following tree map visualizations show the distribution of funding for Themes/Projects/People.  Clicking on a section will zoom in to that section.  If 'Funding' is selected, the area that the section takes up is based on how much funding each section gets.  If 'Count' is selected, the area is based on how many sub-sections each section has.";
	    $phaseDates = $config->getValue('projectPhaseDates');
	    $startYear = substr($phaseDates[1], 0, 4);
	    for($year=$startYear; $year <= REPORTING_YEAR+1; $year++){
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
                $challenges = $project->getChallenges();
                foreach($challenges as $challenge){
                    $theme = ($challenge != null) ? $challenge->getAcronym() : "Unknown";
                    foreach($project->getAllPeopleDuring(null, $year."-01-01", $year."-12-31") as $person){
                        $total = $person->getAllocatedAmount($year, $project);
                        @$projs[$theme][$project->getName()][$person->getNameForForms()] = $total;
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
