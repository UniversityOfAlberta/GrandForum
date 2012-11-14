<?php

abstract class ProjectTab extends AbstractInlineEditableTab {

    var $acronym;
    var $fullName;
    var $proposed;
    var $description;
    var $theme1;
    var $theme2;
    var $theme3;
    var $theme4;
    var $theme5;
    
    function ProjectTab($name){
        parent::AbstractTab($name);
        $this->acronym = (isset($_POST['acronym'])) ? str_replace("'", "&#39;'", trim($_POST['acronym'])) : "";
        $this->fullName = (isset($_POST['fullName'])) ? str_replace("'", "&#39;'", trim($_POST['fullName'])) : "";
        $this->proposed = (isset($_POST['proposed'])) ? str_replace("'", "&#39;'", trim($_POST['proposed'])) : "no";
        $this->description = (isset($_POST['description'])) ? str_replace("'", "&#39;'", trim($_POST['description'])) : "";
        $this->theme1 = (isset($_POST['theme1'])) ? str_replace("'", "&#39;'", trim($_POST['theme1'])) : "";
        $this->theme2 = (isset($_POST['theme2'])) ? str_replace("'", "&#39;'", trim($_POST['theme2'])) : "";
        $this->theme3 = (isset($_POST['theme3'])) ? str_replace("'", "&#39;'", trim($_POST['theme3'])) : "";
        $this->theme4 = (isset($_POST['theme4'])) ? str_replace("'", "&#39;'", trim($_POST['theme4'])) : "";
        $this->theme5 = (isset($_POST['theme5'])) ? str_replace("'", "&#39;'", trim($_POST['theme5'])) : "";
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function validateTheme($theme){
        return ($theme != "" && (!is_numeric($theme) || $theme < 0 || $theme > 100));
    }
    
    function canEdit(){
        return true;
    }
}    
    
?>
