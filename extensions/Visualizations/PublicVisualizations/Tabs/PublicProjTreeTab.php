<?php

$wgHooks['UnknownAction'][] = 'PublicProjTreeTab::getPublicProjTreeData';

class PublicProjTreeTab extends AbstractTab {
	
	function PublicProjTreeTab(){
        parent::AbstractTab("Projects");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getPublicProjTreeData", "Count", "", "", "");
        $tree->height = 600;
        $tree->width = "100%";
        $this->html .= $tree->show();
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'projects'){
                    onLoad{$tree->index}();
                }
            });
            var lastWidth{$tree->index} = 0;
            var lastHeight{$tree->index} = 0;
            setInterval(function(){
                var newWidth = $('#projects').width();
                var newHeight = $('#projects').height();
                if(lastWidth{$tree->index} != newWidth){
                    onLoad{$tree->index}();
                }
                lastWidth{$tree->index} = newWidth;
                lastHeight{$tree->index} = newHeight;
            }, 100);
            </script>
            <p>This tree map shows the distribution of people and projects within themes.  Each level represents a different entity:</p>";
        if($config->getValue('networkName') == "AI4Society"){
            $this->html .= "<ul type='disc'>
                <li>Activities";
        }
        $this->html .= "<ul type='disc'>
                            <li>".Inflect::pluralize($config->getValue('projectThemes'))."
                                <ul type='disc'>
                                    <li>Projects
                                        <ul type='disc'>
                                            <li>People</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>";
        if($config->getValue('networkName') == "AI4Society"){
            $this->html .= "
                </li>
            </ul>";
        }
        $this->html .= "<p>Click to go down a level.  Once at the lowest level, click again to return to the top level.</p>";
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
                $challenges = $project->getChallenges();
                $activities = array(null);
                $themes = $challenges;
                if($config->getValue('networkName') == "AI4Society"){
                    // Handle Activity - Theme structure
                    $themes = array();
                    $activities = array();
                    foreach($challenges as $challenge){
                        if(strstr($challenge->getName(), "Activity - ") !== false){
                            $activities[] = $challenge;
                        }
                        else{
                            $themes[] = $challenge;
                        }
                    }
                }
                foreach($activities as $activity){
                    $activity = ($activity != null) ? $activity->getAcronym() : "Unknown";
                    foreach($themes as $theme){
                        $theme = ($theme != null) ? $theme->getAcronym() : "Unknown";
                        foreach($people as $person){
                            if($person->isRole(NI)){
                                @$projs[$activity][$theme][$project->getName()][$person->getReversedName()] = 1;
                            }
                        }
                    }
                }
            }
            
            foreach($projs as $activity => $projs2){
                if($config->getValue('networkName') == "AI4Society"){
                    $challenge = Theme::newFromName($activity);
                    $color = $challenge->getColor();
                    $activity = ($challenge->getId() != 0) ? $challenge->getName() : $activity;
                    $activityData = array("name" => str_replace("Activity - ", "", $activity),
                                          "color" => $color,
                                          "children" => array());
                }
                foreach($projs2 as $theme => $projs3){
                    $challenge = Theme::newFromName($theme);
                    $color = $challenge->getColor();
                    $theme = ($challenge->getId() != 0) ? $challenge->getName() : $theme;
                    $themeData = array("name" => str_replace("Theme - ", "", $theme),
                                       "color" => $color,
                                       "children" => array());
                    foreach($projs3 as $proj => $person){
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
                    if($config->getValue('networkName') == "AI4Society"){
                        $activityData['children'][] = $themeData;
                    }
                    else{
                        $data['children'][] = $themeData;
                    }
                }
                if($config->getValue('networkName') == "AI4Society"){
                    $data['children'][] = $activityData;
                }
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
