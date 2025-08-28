<?php

abstract class AbstractEditableTab extends AbstractTab {
    
    var $editText = "";
    var $saveText = "";
    
    function __construct($name){
        parent::__construct($name);
        $this->editText = "Edit {$this->name}";
        $this->saveText = "Save {$this->name}";
    }
    
    function showEditButton(){
        global $wgServer, $wgScriptPath, $wgTitle;
        $this->html .= "<br /><input type='hidden' name='edit' value='true'  style='margin-top:2px;' /><button type='submit' name='submit' value='Edit {$this->name}'  style='margin-top:2px;'>{$this->editText}</button>";
    }
    
    function showSaveButton(){
        $this->html .= "<br /><button type='submit' name='submit' onclick='this.form.submitted=this.value;' value='Save {$this->name}' style='margin-top:2px;'>{$this->saveText}</button>";
    }
    
    function showCancelButton(){
        $name = str_replace("'", "&#39;", $this->name);
        $this->html .= "&nbsp;&nbsp;<input id='{$this->id}' type='submit' name='submit' onclick='this.form.submitted=this.value;' value='Cancel' />";
        $this->html .= "<script type='text/javascript'>
            $('input#{$this->id}').click(function(){
                $(\"<input type='hidden' name='cancel' value='{$name}' />\").insertBefore($('input#{$this->id}'));
            });
        </script>";
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
