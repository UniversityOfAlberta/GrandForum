<?php

class EvolveProjectTab extends ProjectTab {

    function EvolveProjectTab(){
        parent::ProjectTab("Evolve");
    }
    
    static function createForm(){
        $projectNames = array();
        $projectNames[] = "NO PROJECT";
        foreach(Project::getAllProjects() as $project){
            $projectNames[] = $project->getName();
        }
        $form = new FormContainer("evolve_project_container");
        
        $projRow = new FormTableRow("evolve_project_row");
        $projRow->append(new Label("evolve_project_label", "Project", VALIDATE_NOT_NULL));
        $projRow->append(new SelectBox("evolve_project", "Project", "NO PROJECT", $projectNames, VALIDATE_NOT_NULL + VALIDATE_IS_PROJECT));
        
        $create = CreateProjectTab::createForm('evolve');
        $create->getElementById("evolve_themes_set")->remove();
        $create->getElementById("evolve_form_table")->insertBefore($projRow, 'evolve_acronym_row');
        
        //$form->append($projSelect);
        $form->append($create);
        
        return $form;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $form = self::createForm('evolve');
        $this->html = $form->render();
        return $this->html;
    }
    
    function handleEdit(){
        global $wgMessages;
        
        $form = self::createForm();
        $errors = $form->validate();
        
        if(count($errors) == 0){
            // Call the API
            $form->getElementById("evolve_acronym")->setPOST("acronym");
            $form->getElementById("evolve_full_name")->setPOST("fullName");
            $form->getElementById("evolve_proposed")->setPOST("proposed");
            $form->getElementById("evolve_description")->setPOST("description");
            $form->getElementById("evolve_theme1")->setPOST("theme1");
            $form->getElementById("evolve_theme2")->setPOST("theme2");
            $form->getElementById("evolve_theme3")->setPOST("theme3");
            $form->getElementById("evolve_theme4")->setPOST("theme4");
            $form->getElementById("evolve_theme5")->setPOST("theme5");
            //APIRequest::doAction('CreateProject', true);
            $form->reset();
        }
        return implode("<br />\n", $errors);
        
    }
}    
    
?>
