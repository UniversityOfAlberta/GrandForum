<?php

class PersonProjectTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonProjectTab($person, $visibility){
        parent::AbstractTab("Projects");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $this->showProjects($this->person, $this->visibility, 'Active');
        $this->showProjects($this->person, $this->visibility, 'Ended');
        if($this->visibility['isSupervisor']){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:EditMember?project&name={$this->person->getName()}\");' value='Edit Projects' />";
        }
        return $this->html;
    }
    
    /*
     * Displays the list of projects for this user
     */
    function showProjects($person, $visibility, $status='Active'){
        global $wgOut, $wgScriptPath, $wgServer;
        if($status == 'Active'){
            $projects = $person->getProjects();
        }
        else if($status == 'Ended'){
            $projects = $person->getProjects(true);
        }
        if($visibility['edit'] || (!$visibility['edit'] && count($projects) > 0)){
            $projs = array();
            foreach($projects as $project){
			    if(!$project->isSubProject() && $project->getStatus() == $status){
				    $subprojs = array();
				    foreach($project->getSubProjects() as $subproject){
				        if($person->isMemberOf($subproject)){
				            $subprojs[] = "<a href='{$subproject->getUrl()}'>{$subproject->getName()}</a>";
				        }
				    }
				    $subprojects = "";
				    if(count($subprojs) > 0){
				        $subprojects = "(".implode(", ", $subprojs).")";
				    }
				    $projs[] = "<li><a href='{$project->getUrl()}'>{$project->getName()}</a> $subprojects</li>";
				}
			}
			if(count($projs) > 0){
			    if($status == 'Active'){
			        $this->html .= "<h3>Current Projects</h3>";
			    }
			    else if($status == 'Ended'){
			        $this->html .= "<h3>Completed Projects</h3>";
			    }
			    $this->html .= "<ul>".implode("\n", $projs)."</ul>";
			}
        }
    }
}
?>
