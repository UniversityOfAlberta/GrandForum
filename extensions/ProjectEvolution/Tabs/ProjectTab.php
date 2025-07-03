<?php

abstract class ProjectTab extends AbstractInlineEditableTab {
    
    function __construct($name){
        parent::__construct($name);
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return true;
    }
}    
    
?>
