<?php

UnknownAction::createAction('PublicProjTreeTab::getPublicProjTreeData');

class PublicProjTreeTab extends AbstractTab {
	
	function PublicProjTreeTab(){
        parent::AbstractTab("Projects");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getPublicProjTreeData", "Count", "", "", "");
        $tree->height = 500;
        $tree->width = 1000;
        $this->html .= $tree->show();
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'projects'){
                    onLoad{$tree->index}();
                }
            });
            </script>";
	}
	
	static function getPublicProjTreeData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getPublicProjTreeData"){
	        session_write_close();  
            $data = array("name" => $config->getValue('networkName'),
                          "children" => array());
            $projs = array();
            $projects = Project::getAllProjects();
            foreach($projects as $project){
                if($project->getType() == "Administrative"){
                    continue;
                }
                $people = $project->getAllPeople();
                $challenge = $project->getChallenge();
                $theme = ($challenge != null) ? $challenge->getAcronym() : "Unknown";
                foreach($people as $person){
                    if($person->isRole(NI)){
                        @$projs[$theme][$project->getName()][$person->getReversedName()] = 1;
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
