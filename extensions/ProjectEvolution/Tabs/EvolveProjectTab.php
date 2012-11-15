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
        $projRow->append(new Label("evolve_project_label", "Project", "Which project to evolve", VALIDATE_NOT_NULL));
        $projRow->append(new SelectBox("evolve_project", "Project", "NO PROJECT", $projectNames, VALIDATE_NOT_NULL + VALIDATE_IS_PROJECT));
        
        $create = CreateProjectTab::createForm('evolve');
        $create->getElementById("evolve_acronym")->validations = VALIDATE_NOT_NULL;
        $create->getElementById("evolve_themes_set")->remove();
        $create->getElementById("evolve_description_row")->remove();
        $create->getElementById("evolve_form_table")->insertBefore($projRow, 'evolve_acronym_row');
        
        //$form->append($projSelect);
        $form->append($create);
        
        return $form;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->html = "'Evolve Project' will allow an already existing project to change to new project (for name changes, status changes etc.)<br />";
        $form = self::createForm();
        $this->html .= $form->render();
        return $this->html;
    }
    
    function handleEdit(){
        global $wgMessages;
        
        $form = self::createForm();
        $errors = $form->validate();
        
        if(count($errors) == 0){
            // Call the API
            $form->getElementById("evolve_project")->setPOST("project");
            $form->getElementById("evolve_acronym")->setPOST("acronym");
            $form->getElementById("evolve_full_name")->setPOST("fullName");
            $form->getElementById("evolve_status")->setPOST("status");
            $form->getElementById("evolve_type")->setPOST("type");
            $form->getElementById("evolve_effective")->setPOST("effective_date");
            APIRequest::doAction('EvolveProject', true);
            $form->reset();
        }
        return implode("<br />\n", $errors);
        
    }
}    
    
?>
