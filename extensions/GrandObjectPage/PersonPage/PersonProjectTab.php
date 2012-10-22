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
            $this->html .= "<ul>";
            foreach($person->getProjects() as $project){
                $this->html .= "<li><a href='{$project->getUrl()}'>{$project->getName()} - {$project->getFullName()}</a></li>\n";
            }
            $this->html .= "</ul>";
            if($visibility['isSupervisor']){
                $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:EditMember?project&name={$person->getName()}\");' value='Edit Projects' />";
            }
        }
    }
}
?>
