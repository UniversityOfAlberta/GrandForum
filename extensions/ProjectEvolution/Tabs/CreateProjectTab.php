<?php

class CreateProjectTab extends AbstractInlineEditableTab {

    var $acronym;
    var $fullName;
    var $proposed;
    var $description;
    var $theme1;
    var $theme2;
    var $theme3;
    var $theme4;
    var $theme5;

    function CreateProjectTab(){
        parent::AbstractTab("Create Project");
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
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $yesSelected = "";
        $noSelected = " checked";
        if($this->proposed == "yes"){
            $yesSelected = " checked";
            $noSelected = "";
        }
        $this->html = "<table>";
        $this->html .= "<tr><td class='tooltip label' title='The acronym/name for the project ie. MEOW'>Acronym<span style='color:red;'>*</span>:</td><td><input type='text' name='acronym' value='{$this->acronym}' /></td></tr>";
        $this->html .= "<tr><td class='tooltip label' title='The project&#39;s full name ie. Media Enabled Organizational Worldflow' class='tooltip'>Full Name<span style='color:red;'>*</span>:</td><td><input style='width:400px;' type='text' name='fullName' value='{$this->fullName}' /></td></tr>";
        $this->html .= "<tr><td class='tooltip label' title='Whether or not this project is proposed(loi) or not'>Proposed?<span style='color:red;'>*</span>:</td><td><input type='radio' name='proposed' value='yes' $yesSelected />Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='proposed' value='no' $noSelected />No</td></tr>";
        $this->html .= "<tr><td class='tooltip label' title='The description of the project' class='tooltip'>Description:</td><td><textarea style='width:408px;height:100px;' name='description'>{$this->description}</textarea></td></tr>";
        $this->html .= "<tr><td colspan='2'><fieldset><legend>Themes</legend>
                            <table>
                                <tr><td class='label'>AnImage:</td><td><input type='text' name='theme1' size='3' value='{$this->theme1}' />%</td></tr>
                                <tr><td class='label'>GamSim:</td><td><input type='text' name='theme2' size='3' value='{$this->theme2}' />%</td></tr>
                                <tr><td class='label'>nMEDIA:</td><td><input type='text' name='theme3' size='3' value='{$this->theme3}' />%</td></tr>
                                <tr><td class='label'>SocLeg:</td><td><input type='text' name='theme4' size='3' value='{$this->theme4}' />%</td></tr>
                                <tr><td class='label'>TechMeth:</td><td><input type='text' name='theme5' size='3' value='{$this->theme5}' />%</td></tr>
                            </table></fieldset></td></tr>";
        $this->html .= "</table>";
        return $this->html;
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function handleEdit(){
        global $wgMessages;
        $errors = array();
        if($this->acronym == ""){
            $errors[] = "An 'Acronym' must be provided";
        }
        else{
            $project = Project::newFromName($this->acronym);
            if($project != null && $project->getName() != ""){
                $errors[] = "The project '{$this->acronym}' already exists";
            }
        }
        if($this->fullName == ""){
            $errors[] = "A 'Full Name' must be provided";
        }
        if(CreateProjectTab::validateTheme($this->theme1)){
            $errors[] = "'AnImage' must be a number between 0 and 100";
        }
        if(CreateProjectTab::validateTheme($this->theme2)){
            $errors[] = "'GamSim' must be a number between 0 and 100";
        }
        if(CreateProjectTab::validateTheme($this->theme3)){
            $errors[] = "'nMEDIA' must be a number between 0 and 100";
        }
        if(CreateProjectTab::validateTheme($this->theme4)){
            $errors[] = "'SocLeg' must be a number between 0 and 100";
        }
        if(CreateProjectTab::validateTheme($this->theme5)){
            $errors[] = "'TechMeth' must be a number between 0 and 100";
        }
        
        if(count($errors) == 0){
            // Call the API
            
            $this->acronym = "";
            $this->fullName = "";
            $this->description = "";
            $this->theme1 = "";
            $this->theme2 = "";
            $this->theme3 = "";
            $this->theme4 = "";
            $this->theme5 = "";
        }
        return implode("<br />\n", $errors);
    }
    
    function validateTheme($theme){
        return ($theme != "" && (!is_numeric($theme) || $theme < 0 || $theme > 100));
    }
    
    function canEdit(){
        return true;
    }
}    
    
?>
