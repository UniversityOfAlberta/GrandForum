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
        $projRow->append(new Label("evolve_project_label", "From", "Which project to evolve", VALIDATE_NOT_NULL));
        $projRow->append(new ComboBox("evolve_project", "From", "NO PROJECT", $projectNames, VALIDATE_NOT_NULL + VALIDATE_PROJECT));
        
        $newProjRow = new FormTableRow("evolve_project_new_row");
        $newProjRow->append(new Label("evolve_project_new_label", "To", "The project to evolve to", VALIDATE_NOT_NULL));
        $newProjRow->append(new ComboBox("evolve_acronym", "To", "NO PROJECT", $projectNames, VALIDATE_NOT_NULL + VALIDATE_PROJECT));
        
        $clearData = new FormTableRow("evolve_clear_row");
        $clearData->append(new Label("evolve_clear_label", "Clear Data?", "Whether or not to use fresh data for the new project, or to carry over the past data", VALIDATE_NOT_NULL));
        $clearData->append(new VerticalRadioBox("evolve_clear", "Clear Data?", "Yes", array("Yes", "No"), VALIDATE_NOT_NULL));
        
        $create = CreateProjectTab::createForm('evolve');
        $create->getElementById("evolve_subproject_row")->remove();
        $create->getElementById("evolve_subprojectdd_row")->remove();
        $create->getElementById("evolve_challenges_set")->remove();
        $create->getElementById("evolve_pl_row")->remove();
        $create->getElementById("evolve_description_row")->remove();
        $create->getElementById("evolve_long_description_row")->remove();

        $create->getElementById("evolve_form_table")->insertBefore($projRow, 'evolve_acronym_row');
        $create->getElementById("evolve_form_table")->insertBefore($newProjRow, 'evolve_acronym_row');
        $create->getElementById("evolve_form_table")->insertBefore($clearData, 'evolve_effective_row');
        $create->getElementById("evolve_acronym_row")->remove();
        $create->getElementById("evolve_full_name_row")->remove();
        $create->getElementById("evolve_type_row")->remove();
        $create->getElementById("evolve_status_row")->remove();
        $create->getElementById("evolve_phase_row")->remove();
        //$create->getElementById("evolve_champ_row")->remove();

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
        $status = $form->validate();
        if($status){
            // Call the API
            $form->getElementById("evolve_project")->setPOST("project");
            $form->getElementById("evolve_acronym")->setPOST("acronym");
            $form->getElementById("evolve_clear")->setPOST("clear");
            $form->getElementById("evolve_effective")->setPOST("effective_date");
            $_POST['action'] = "EVOLVE";
            if(!APIRequest::doAction('EvolveProject', true)){
                return "There was an error Evolving the Project";
            }
            else{
                $form->reset();
            }
        }
        else{
            return "The Project was not evolved";
        }
    }
}    
    
?>
