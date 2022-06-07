<?php

class InactivateProjectTab extends ProjectTab {

    function __construct(){
        ProjectTab::__construct("Inactivate");
    }
    
    static function createForm(){
        $projectNames = array();
        $projectNames[] = "NO PROJECT";
        foreach(Project::getAllProjects(true) as $project){
            $projectNames[] = $project->getName();
        }
        $form = new FormContainer("delete_project_container");
        
        $projRow = new FormTableRow("delete_project_row");
        $projRow->append(new Label("delete_project_label", "Project", "Which project to evolve", VALIDATE_NOT_NULL));
        $projRow->append(new ComboBox("delete_project", "Project", "NO PROJECT", $projectNames, VALIDATE_NOT_NULL + VALIDATE_PROJECT));
        
        $create = CreateProjectTab::createForm('delete');
        
        $create->getElementById("delete_acronym_row")->remove();
        $create->getElementById("delete_subproject_row")->remove();
        $create->getElementById("delete_subprojectdd_row")->remove();
        $create->getElementById("delete_challenges_set")->remove();
        $create->getElementById("delete_pl_row")->remove();
        $create->getElementById("delete_description_row")->remove();
        $create->getElementById("delete_long_description_row")->remove();
        $create->getElementById("delete_full_name_row")->remove();
        $create->getElementById("delete_status_row")->remove();
        $create->getElementById("delete_type_row")->remove();
        $create->getElementById("delete_phase_row")->remove();
        //$create->getElementById("delete_champ_row")->remove();
        $create->getElementById("delete_form_table")->prepend($projRow, 'delete_acronym_row');

        $form->append($create);
        
        return $form;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->html = "'Inactivate Project' will allow an already existing project to be inactivated.<br />";
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
            $form->getElementById("delete_project")->setPOST("project");
            $form->getElementById("delete_effective")->setPOST("effective_date");
            
            if(!APIRequest::doAction('DeleteProject', true)){
                return "There was an error Inactivating the Project";
            }
            else{
                $form->reset();
            }
        }
        else{
            return "The Project was not inactivated";
        }
    }
}    
    
?>
