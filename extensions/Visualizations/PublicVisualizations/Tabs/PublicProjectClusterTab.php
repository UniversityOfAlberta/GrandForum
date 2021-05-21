<?php

$wgHooks['UnknownAction'][] = 'PublicProjectClusterTab::getProjectClusterData';

class PublicProjectClusterTab extends AbstractTab {
	
	function PublicProjectClusterTab(){
        parent::AbstractTab("Project Cluster");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
        $cluster = new Cluster("{$wgServer}{$wgScriptPath}/index.php?action=getProjectClusterData");
        $cluster->height = 750;
        $cluster->width = 750;
        $this->html .= "<div style='display:inline-block;'>{$cluster->show()}</div>";
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'project-cluster'){
                    onLoad{$cluster->index}();
                }
            });
            </script>";
        $this->html .= "<div style='display:inline-block;'><h3>Help</h3>
            <p>This visualization shows which projects are in each ".strtolower($config->getValue('projectThemes')).".  Click on a ".strtolower($config->getValue('projectThemes'))." or project to take you to its page.</p></div>";
	}
	
	static function getProjectClusterData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    if($action == "getProjectClusterData"){
	        session_write_close();
	        $data = array("name" => "",
	                      "children" => array());
	        
	        $themes = array();
	        $projects = Project::getAllProjectsEver();
	        foreach($projects as $project){
                $challenges = $project->getChallenges();
                foreach($challenges as $theme){
                    @$themes[$theme->getAcronym()][$project->getId()] = $project;
                }
	        }
	        
	        foreach($themes as $name => $projs){
	            if($config->getValue('networkName') == "AI4Society" && strstr($name, "Activity - ") !== false){ continue; }
	            $theme = Theme::newFromName($name);
	            $tFullName = $theme->getName();
	            $tDesc = $theme->getDescription();
	            $tleaders = $theme->getLeaders();
	            $color = $theme->getColor();
	            $turl = $theme->getUrl();
	            $image = "";
	            switch($name){
	                case "(Big) Data":
	                    $image = "data.png";
	                    break;
	                case "Citizenship":
	                    $image = "citizenship.png";
	                    break;
	                case "Entertainment":
	                    $image = "entertainment.png";
	                    break;
	                case "Health":
	                    $image = "health.png";
	                    break;
	                case "Learning":
	                    $image = "learning.png";
	                    break;
	                case "Sustainability":
	                    $image = "sustainability.png";
	                    break;
	                case "Work":
	                    $image = "work.png";
	                    break;
	            }
	            
	            $themeChildren = array();
	            foreach($projs as $proj){
	                $subs = $proj->getSubProjects();
	                $projChildren = array();
	                $pleaders = $proj->getLeaders();
	                foreach($subs as $sub){
	                    $sleaders = $sub->getLeaders();
	                    $slead = array("name" => "",
	                                   "uni" => "");
	                    if(count($sleaders) > 0){
	                        $sleaders = array_values($sleaders);
	                        $slead['name'] = $sleaders[0]->getNameForForms();
	                        $slead['uni'] = $sleaders[0]->getUni();
	                    }
	                    $projChildren[] = array("name" => $sub->getName(),
	                                            "fullname" => $sub->getFullName(),
	                                            "description" => $sub->getDescription(),
	                                            "color" => $color,
	                                            "url" => $sub->getUrl(),
	                                            "leader" => $slead);
	                }
	                $plead = array("name" => "",
	                               "uni" => "");
	                if(count($pleaders) > 0){
	                    $pleaders = array_values($pleaders);
	                    $plead['name'] = $pleaders[0]->getNameForForms();
	                    $plead['uni'] = $pleaders[0]->getUni();
	                }
	                $themeChildren[] = array("name" => $proj->getName(),
	                                         "fullname" => $proj->getFullName(),
	                                         "description" => $proj->getDescription(),
	                                         "color" => $color,
	                                         "url" => $proj->getUrl(),
	                                         "leader" => $plead,
	                                         "children" => $projChildren);
	            }
	            
	            $tlead = array("name" => "",
	                           "uni" => "");
	            if(count($tleaders) > 0){
	                $tleaders = array_values($tleaders);
	                $tlead['name'] = $tleaders[0]->getNameForForms();
	                $tlead['uni'] = $tleaders[0]->getUni();
	            }
	            if($image != ""){
	                $image = "{$wgServer}{$wgScriptPath}/extensions/Visualizations/Cluster/images/{$image}";
	                $data['children'][] = array("name" => $name,
	                                            "fullname" => $tFullName,
	                                            "description" => $tDesc,
	                                            "color" => $color,
	                                            "image" => $image,
	                                            "url" => $turl,
	                                            "text" => "below",
	                                            "leader" => $tlead,
	                                            "children" => $themeChildren);
	            }
	            else{
	                $data['children'][] = array("name" => $name,
	                                            "fullname" => $tFullName,
	                                            "color" => $color,
	                                            "url" => $turl,
	                                            "leader" => $tlead,
	                                            "children" => $themeChildren);
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
