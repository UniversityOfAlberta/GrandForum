<?php

UnknownAction::createAction('PublicProjectClusterTab::getProjectClusterData');

class PublicProjectClusterTab extends AbstractTab {
	
	function __construct(){
        parent::__construct("Project Cluster");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
        $cluster = new Cluster("{$wgServer}{$wgScriptPath}/index.php?action=getProjectClusterData");
        $cluster->height = 900;
        $cluster->width = 900;
        $this->html .= "{$cluster->show()}";
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'project-cluster'){
                    onLoad{$cluster->index}();
                }
            });
            </script>
            <p>This visualization shows which projects are in each ".strtolower($config->getValue('projectThemes', 1)).".  Click on a ".strtolower($config->getValue('projectThemes', 1))." or project to take you to its page.</p>";
	}
	
	static function getProjectClusterData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    if($action == "getProjectClusterData"){
	        session_write_close();
	        $data = array("name" => "",
	                      "children" => array());
	        
	        $themes = array();
	        $projects = Project::getAllProjects();
	        foreach($projects as $project){
	            if($project->getType() == "Administrative"){
                    continue;
                }
                $challenges = $project->getChallenges();
                foreach($challenges as $theme){
                    @$themes[$theme->getAcronym()][$project->getId()] = $project;
                }
	        }
	        
	        foreach($themes as $name => $projs){
	            if($config->getValue('networkName') == "AI4Society" && strstr($name, "Activity - ") !== false){ continue; }
	            $theme = Theme::newFromName($name);
	            $color = $theme->getColor();
	            $themeChildren = array();
	            foreach($projs as $proj){
	                $activity = $proj->getActivity();
	                $pName = $proj->getName();
	                if($config->getValue('networkName') == "AI4Society"){
	                    $pName = $proj->getShortFullName();
	                }
	                $project = array("name" => $pName,
                                     "fullname" => $proj->getFullName(),
                                     "color" => $color,
                                     "url" => $proj->getUrl(),
                                     "children" => array());
	                if($activity->getAcronym() != "Not Specified"){
	                    if(!isset($themeChildren[$activity->getId()])){
	                        $tname = ($config->getValue('networkName') == "AI4Society") ? str_replace("Activity - ", "", $activity->getName()) : $activity->getAcronym();
	                        $themeChildren[$activity->getId()] = array("name" => $tname,
                                                                       "fullname" => $activity->getName(),
                                                                       "color" => $activity->getColor(),
                                                                       "url" => $activity->getUrl(),
                                                                       "children" => array());
	                    }
	                    $project['color'] = $activity->getColor();
	                    $themeChildren[$activity->getId()]['children'][] = $project;
	                }
	                else{
	                    $themeChildren[] = $project;
	                }
	            }
	            $tname = ($config->getValue('networkName') == "AI4Society") ? str_replace("Theme - ", "", $theme->getName()) : $theme->getAcronym();
                $data['children'][] = array("name" => $tname,
                                            "fullname" => $theme->getName(),
                                            "color" => $theme->getColor(),
                                            "url" => $theme->getUrl(),
                                            "children" => array_values($themeChildren));
	        }
	        
	        header("Content-Type: application/json");
	        echo json_encode($data);
	        exit;
        }
        return true;
	}
}
?>
