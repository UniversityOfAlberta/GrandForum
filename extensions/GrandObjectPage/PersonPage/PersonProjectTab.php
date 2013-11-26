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
        $this->showProjects($this->person, $this->visibility);
        return $this->html;
    }
    
    /*
     * Displays the list of projects for this user
     */
    function showProjects($person, $visibility){
        global $wgOut, $wgScriptPath, $wgServer;
        if($visibility['edit'] || (!$visibility['edit'] && count($person->getProjects()) > 0)){
            foreach($person->getProjects() as $project){
			    if(!$project->isSubProject()){
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
            $this->html .= "<ul>".implode("\n", $projs)."</ul>";
            if($visibility['isSupervisor']){
                $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:EditMember?project&name={$person->getName()}\");' value='Edit Projects' />";
            }
        }
    }
}
?>
