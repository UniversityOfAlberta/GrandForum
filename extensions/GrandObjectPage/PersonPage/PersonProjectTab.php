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
        for($phase=PROJECT_PHASE; $phase > 0; $phase--){
            $this->showProjects($this->person, $this->visibility, $phase);
        }
        if($this->visibility['isSupervisor']){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:EditMember?project&name={$this->person->getName()}\");' value='Edit Projects' />";
        }
        return $this->html;
    }
    
    /*
     * Displays the list of projects for this user
     */
    function showProjects($person, $visibility, $phase=1){
        global $wgOut, $wgScriptPath, $wgServer;
        if($visibility['edit'] || (!$visibility['edit'] && count($person->getProjects()) > 0)){
            $projs = array();
            foreach($person->getProjects() as $project){
			    if(!$project->isSubProject() && $project->getPhase() == $phase){
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
			    $this->html .= "<h3>Phase $phase</h3>";
			    $this->html .= "<ul>".implode("\n", $projs)."</ul>";
			}
        }
    }
}
?>
