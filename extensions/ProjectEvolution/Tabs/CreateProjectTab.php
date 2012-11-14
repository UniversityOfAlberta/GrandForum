<?php

class CreateProjectTab extends ProjectTab {

    function CreateProjectTab(){
        parent::ProjectTab("Create");
    }
    
    function createForm(){
    
        $table = new FormTable();
        
        $acronymRow = new FormTableRow();
        $acronymRow->addElement(new Label("new_acronym_label", "Acronym", "The acronym/name for the project ie. MEOW", VALIDATE_NOT_NULL));
        $acronymRow->addElement(new TextField("new_acronym", "Acronym", "", 12, VALIDATE_NOT_NULL));
        
        $fullNameRow = new FormTableRow();
        $fullNameRow->addElement(new Label("new_full_name_label", "Full Name", "The project's full name ie. Media Enabled Organizational Worldflow", VALIDATE_NOT_NULL));
        $fullNameRow->addElement(new TextField("new_full_name", "Full Name", "", 40, VALIDATE_NOT_NULL));
        
        $proposedRow = new FormTableRow();
        $proposedRow->addElement(new Label("new_proposed_label", "Proposed?", "Whether or not this project is proposed(loi) or not", VALIDATE_NOT_NULL));
        $proposedRow->addElement(new RadioField("new_proposed", "Proposed?", "No", array("Yes", "No"), VALIDATE_NOT_NULL));
        
        $descRow = new FormTableRow();
        $descRow->addElement(new Label("new_description_label", "Description", "The description of the project", VALIDATE_NOTHING));
        $descRow->addElement(new TextareaField("new_description", "Description", "", VALIDATE_NOTHING));
        
        $theme1Row = new FormTableRow();
        $theme1Row->addElement(new Label("new_theme1_label", "AnImage", "", VALIDATE_NOTHING));
        $theme1Row->addElement(new PercentField("new_theme1", "AnImage", "", VALIDATE_NOTHING));
        
        $theme2Row = new FormTableRow();
        $theme2Row->addElement(new Label("new_theme2_label", "GamSim", "", VALIDATE_NOTHING));
        $theme2Row->addElement(new PercentField("new_theme2", "GamSim", "", VALIDATE_NOTHING));
        
        $theme3Row = new FormTableRow();
        $theme3Row->addElement(new Label("new_theme3_label", "nMEDIA", "", VALIDATE_NOTHING));
        $theme3Row->addElement(new PercentField("new_theme3", "nMEDIA", "", VALIDATE_NOTHING));
        
        $theme4Row = new FormTableRow();
        $theme4Row->addElement(new Label("new_theme4_label", "SocLeg", "", VALIDATE_NOTHING));
        $theme4Row->addElement(new PercentField("new_theme4", "SocLeg", "", VALIDATE_NOTHING));
        
        $theme5Row = new FormTableRow();
        $theme5Row->addElement(new Label("new_theme5_label", "TechMeth", "", VALIDATE_NOTHING));
        $theme5Row->addElement(new PercentField("new_theme5", "TechMeth", "", VALIDATE_NOTHING));
        
        $table->addElement($acronymRow);
        $table->addElement($fullNameRow);
        $table->addElement($proposedRow);
        $table->addElement($descRow);
        $table->addElement($theme1Row);
        $table->addElement($theme2Row);
        $table->addElement($theme3Row);
        $table->addElement($theme4Row);
        $table->addElement($theme5Row);
        
        return $table;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $yesSelected = "";
        $noSelected = " checked";
        if($this->proposed == "true"){
            $yesSelected = " checked";
            $noSelected = "";
        }
        
        
        $table = $this->createForm();
        $this->html = $table->render();
        return $this->html;
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
    
    function handleEdit(){
        global $wgMessages;
        $table = $this->createForm();
        $errors = $table->validate();
        
        if(count($errors) == 0){
            // Call the API
            $table->getElementById("new_acronym")->setPOST("acronym");
            $table->getElementById("new_full_name")->setPOST("fullName");
            $table->getElementById("new_proposed")->setPOST("proposed");
            $table->getElementById("new_description")->setPOST("description");
            $table->getElementById("new_theme1")->setPOST("theme1");
            $table->getElementById("new_theme2")->setPOST("theme2");
            $table->getElementById("new_theme3")->setPOST("theme3");
            $table->getElementById("new_theme4")->setPOST("theme4");
            $table->getElementById("new_theme5")->setPOST("theme5");
            APIRequest::doAction('CreateProject', true);
            $table->reset();
        }
        return implode("<br />\n", $errors);
    }
}    
    
?>
