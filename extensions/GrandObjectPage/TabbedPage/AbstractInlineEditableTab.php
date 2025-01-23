<?php

abstract class AbstractInlineEditableTab extends AbstractEditableTab {
    
    function __construct($name){
        parent::__construct($name);
    }
    
    function showEditButton(){
        global $wgServer, $wgScriptPath, $wgTitle;
        $this->html .= "<br /><input type='submit' name='submit' value='{$this->name}' />";
    }
    
    function showSaveButton(){
        $this->html .= "<br /><input type='submit' name='submit' value='{$this->name}' />";
    }
    
    function showCancelButton(){
        $this->html .= "";
    }
}

?>
