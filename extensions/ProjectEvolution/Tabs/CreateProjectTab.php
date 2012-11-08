<?php

class CreateProjectTab extends AbstractInlineEditableTab {

    function CreateProjectTab(){
        parent::AbstractTab("Create Project");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->html = "HELLO";
        return $this->html;
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function handleEdit(){
        
    }
    
    function canEdit(){
        return true;
    }
}    
    
?>
