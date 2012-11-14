<?php

class CreateProjectTab extends ProjectTab {

    function CreateProjectTab(){
        parent::ProjectTab("Create");
    }
    
    static function createForm($pre){
    
        $form = new FormContainer();
    
        $table = new FormTable();
        
        $acronymRow = new FormTableRow();
        $acronymRow->addElement(new Label("{$pre}_acronym_label", "Acronym", "The acronym/name for the project ie. MEOW", VALIDATE_NOT_NULL));
        $acronymRow->addElement(new TextField("new_acronym", "Acronym", "", 12, VALIDATE_NOT_NULL));
        
        $fullNameRow = new FormTableRow();
        $fullNameRow->addElement(new Label("{$pre}_full_name_label", "Full Name", "The project's full name ie. Media Enabled Organizational Worldflow", VALIDATE_NOT_NULL));
        $fullNameRow->addElement(new TextField("new_full_name", "Full Name", "", 40, VALIDATE_NOT_NULL));
        
        $proposedRow = new FormTableRow();
        $proposedRow->addElement(new Label("{$pre}_proposed_label", "Proposed?", "Whether or not this project is proposed(loi) or not", VALIDATE_NOT_NULL));
        $proposedRow->addElement(new RadioField("{$pre}_proposed", "Proposed?", "No", array("Yes", "No"), VALIDATE_NOT_NULL));
        
        $descRow = new FormTableRow();
        $descRow->addElement(new Label("{$pre}_description_label", "Description", "The description of the project", VALIDATE_NOTHING));
        $descRow->addElement(new TextareaField("{$pre}_description", "Description", "", VALIDATE_NOTHING));
        
        $themeFieldSet = new FieldSet("Themes");
        $themeTable = new FormTable();
        
        $theme1Row = new FormTableRow();
        $theme1Row->addElement(new Label("{$pre}_theme1_label", "AnImage", "", VALIDATE_NOTHING));
        $theme1Row->addElement(new PercentField("{$pre}_theme1", "AnImage", "", VALIDATE_NOTHING));
        
        $theme2Row = new FormTableRow();
        $theme2Row->addElement(new Label("{$pre}_theme2_label", "GamSim", "", VALIDATE_NOTHING));
        $theme2Row->addElement(new PercentField("{$pre}_theme2", "GamSim", "", VALIDATE_NOTHING));
        
        $theme3Row = new FormTableRow();
        $theme3Row->addElement(new Label("{$pre}_theme3_label", "nMEDIA", "", VALIDATE_NOTHING));
        $theme3Row->addElement(new PercentField("{$pre}_theme3", "nMEDIA", "", VALIDATE_NOTHING));
        
        $theme4Row = new FormTableRow();
        $theme4Row->addElement(new Label("{$pre}_theme4_label", "SocLeg", "", VALIDATE_NOTHING));
        $theme4Row->addElement(new PercentField("{$pre}_theme4", "SocLeg", "", VALIDATE_NOTHING));
        
        $theme5Row = new FormTableRow();
        $theme5Row->addElement(new Label("{$pre}_theme5_label", "TechMeth", "", VALIDATE_NOTHING));
        $theme5Row->addElement(new PercentField("{$pre}_theme5", "TechMeth", "", VALIDATE_NOTHING));
        
        $themeTable->addElement($theme1Row);
        $themeTable->addElement($theme2Row);
        $themeTable->addElement($theme3Row);
        $themeTable->addElement($theme4Row);
        $themeTable->addElement($theme5Row);
        $themeFieldSet->addElement($themeTable);
        
        $table->addElement($acronymRow);
        $table->addElement($fullNameRow);
        $table->addElement($proposedRow);
        $table->addElement($descRow);
        
        $form->addElement($table);
        $form->addElement($themeFieldSet);
        
        return $form;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $form = self::createForm('new');
        $this->html = $form->render();
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
        $form = self::createForm('new');
        $errors = $form->validate();
        
        if(count($errors) == 0){
            // Call the API
            $form->getElementById("new_acronym")->setPOST("acronym");
            $form->getElementById("new_full_name")->setPOST("fullName");
            $form->getElementById("new_proposed")->setPOST("proposed");
            $form->getElementById("new_description")->setPOST("description");
            $form->getElementById("new_theme1")->setPOST("theme1");
            $form->getElementById("new_theme2")->setPOST("theme2");
            $form->getElementById("new_theme3")->setPOST("theme3");
            $form->getElementById("new_theme4")->setPOST("theme4");
            $form->getElementById("new_theme5")->setPOST("theme5");
            APIRequest::doAction('CreateProject', true);
            $form->reset();
        }
        return implode("<br />\n", $errors);
    }
}    
    
?>
