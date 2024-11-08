<?php

class PersonProjectTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Projects");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $this->showThemes($this->person, $this->visibility);
        $this->showProjects($this->person, $this->visibility, 'Active', 'Administrative');
        $this->showProjects($this->person, $this->visibility, 'Active');
        $this->showProjects($this->person, $this->visibility, 'Ended');
        if($this->visibility['isSupervisor']){
            $this->html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ManagePeople?project&name={$this->person->getName()}'>Manage People</a>";
        }
        return $this->html;
    }
    
    /**
     * Displays the list of projects for this user
     */
    function showProjects($person, $visibility, $status='Active', $type='Research'){
        global $wgOut, $wgScriptPath, $wgServer, $config;
        if($status == 'Active'){
            $projects = $person->getProjects();
        }
        else if($status == 'Ended'){
            $projects = $person->getProjects(true);
        }
        if($visibility['edit'] || (!$visibility['edit'] && count($projects) > 0)){
            $projs = array();
            foreach($projects as $project){
			    if(!$project->isSubProject() && 
			        $project->getStatus() == $status &&
			        $project->getType() == $type){
				    $subprojs = array();
				    foreach($project->getSubProjects() as $subproject){
				        if($person->isMemberOf($subproject)){
				            $role = $person->getRoleOn($subproject);
				            $subprojs[] = "<a href='{$subproject->getUrl()}'>{$subproject->getFullName()} ({$subproject->getName()})</a> ({$role})";
				        }
				    }
				    $subprojects = "";
				    if(count($subprojs) > 0){
				        $subprojects = "<ul><li>".implode("</li><li>", $subprojs)."</li></ul>";
				    }
				    $role = $person->getRoleOn($project);
				    $projs[] = "<li><a href='{$project->getUrl()}'>{$project->getFullName()} ({$project->getName()})</a> ({$role}) $subprojects</li>";
				}
			}
			if(count($projs) > 0){
			    if($type == 'Research'){
			        if($status == 'Active'){
			            $this->html .= "<h3>Current Projects</h3>";
			        }
			        else if($status == 'Ended'){
			            $this->html .= "<h3>Completed Projects</h3>";
			        }
			    }
			    else if($type == 'Administrative'){
			        if($status == 'Active'){
			            $this->html .= "<h3>{$config->getValue('adminProjects')}</h3>";
			        }
			    }
			    else {
			        if($status == 'Active'){
			            $this->html .= "<h3>{$type} Projects</h3>";
			        }
			    }
			    $this->html .= "<ul>".implode("\n", $projs)."</ul>";
			}
        }
    }
    
    function showThemes($person, $visibility){
        global $wgOut, $wgScriptPath, $wgServer, $config;
        $leadThemes = $person->getLeadThemes();
        $coordThemes = $person->getCoordThemes();
        $projects = $person->getProjects();
        $themes = array();
        foreach($projects as $project){
            if($project->getType() == "Administrative"){
                continue;
            }
            foreach($project->getChallenges() as $theme){
                if($theme->getAcronym() != ""){
                    $themes[$theme->getAcronym()] = $theme;
                }
            }
        }
        foreach($leadThemes as $theme){
            if($theme->getAcronym() != ""){
                $themes[$theme->getAcronym()] = $theme;
            }
        }
        foreach($coordThemes as $theme){
            if($theme->getAcronym() != ""){
                $themes[$theme->getAcronym()] = $theme;
            }
        }
        if(count($themes) > 0){
            $themeNames = array();
            foreach($themes as $theme){
                $lead = "";
                if(isset($leadThemes[$theme->getId()])){
                    $lead = " (lead)";
                }
                else if(isset($coordThemes[$theme->getId()])){
                    $lead = " (coord)";
                }
                $themeNames[] = "<li><a href='{$theme->getUrl()}'>{$theme->getName()} ({$theme->getAcronym()})</a>{$lead}</li>";
            }
            $this->html .= "<h3>".Inflect::pluralize($config->getValue('projectThemes', 1))."</h3>";
            $this->html .= "<ul>".implode("\n", $themeNames)."</ul>";
        }
    }
}
?>
