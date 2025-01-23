<?php

abstract class AbstractEditableTab extends AbstractTab {
    
    function __construct($name){
        parent::__construct($name);
    }
    
    function showEditButton(){
        global $wgServer, $wgScriptPath, $wgTitle;
        $this->html .= "<br /><input type='hidden' name='edit' value='true' /><input type='submit' name='submit' value='Edit {$this->name}' />";
    }
    
    function showSaveButton(){
        $this->html .= "<br /><input type='submit' name='submit' value='Save {$this->name}' />";
    }
    function showCancelButton(){
        $this->html .= "&nbsp;&nbsp;<input type='submit' name='submit' value='Cancel' />";
    }
    
    // Processes the POST data and updates the DB
    // Returns an error message if there was an error, nothing if it was successful
    abstract function handleEdit();
    
    // Generates the HTML for the editing page
    abstract function generateEditBody();
    
    // Returns true if the user has permissions to edit the page, false if otherwise
    abstract function canEdit();
}

?>
