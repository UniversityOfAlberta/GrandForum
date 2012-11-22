<?php

abstract class ProjectTab extends AbstractInlineEditableTab {
    
    function ProjectTab($name){
        parent::AbstractTab($name);
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return true;
    }
}    
    
?>
