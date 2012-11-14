<?php

class EvolveProjectTab extends ProjectTab {

    function EvolveProjectTab(){
        parent::ProjectTab("Evolve");
        
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        return $this->html;
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function handleEdit(){
        global $wgMessages;
        
    }
}    
    
?>
