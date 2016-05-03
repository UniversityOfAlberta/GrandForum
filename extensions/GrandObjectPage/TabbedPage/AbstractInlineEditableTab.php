<?php

abstract class AbstractInlineEditableTab extends AbstractEditableTab {
    
    function AbstractInlineEditableTab($name){
        parent::AbstractTab($name);
    }
    
    function showEditButton(){
        global $wgServer, $wgScriptPath, $wgTitle;
        $this->html .= "<br /><input id='Save{$this->name}' type='submit' name='submit' value='{$this->name}' />";
    }
    
    function showSaveButton(){
        $this->html .= "<br /><input id='Save{$this->name}' type='submit' name='submit' value='{$this->name}' />";
    }
    
    function showCancelButton(){
        $this->html .= "";
    }
}

?>
