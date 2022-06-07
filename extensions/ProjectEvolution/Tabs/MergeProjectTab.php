<?php

class MergeProjectTab extends ProjectTab {

    function __construct(){
        ProjectTab::__construct("Merge");
    }
    
    static function createForm(){
        $projectNames = array();
        $projectNames[] = "NO PROJECT";
        foreach(Project::getAllProjects() as $project){
            $projectNames[] = $project->getName();
        }
        $form = new FormContainer("merge_project_container");
        
        $projRow = new FormTableRow("merge_projects_row");
        $projRow->append(new Label("merge_projects_label", "Projects", "Which projects to merge (Hold Ctrl to select multiple projects)", VALIDATE_NOT_NULL));
        $projRow->append(new MultiSelectBox("merge_projects", "Projects", "NO PROJECT", $projectNames, VALIDATE_NOT_NULL + VALIDATE_PROJECT));
        
        $create = CreateProjectTab::createForm('merge');
        $create->getElementById("merge_acronym")->validations = VALIDATE_NOT_NULL;
        //$create->getElementById("merge_themes_set")->remove();
        $create->getElementById("merge_challenges_set")->remove();
        $create->getElementById("merge_subproject_row")->remove();
        $create->getElementById("merge_subprojectdd_row")->remove();
        $create->getElementById("merge_description_row")->remove();
        $create->getElementById("merge_form_table")->insertBefore($projRow, 'merge_acronym_row');
        
        //$form->append($projSelect);
        $form->append($create);
        
        return $form;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->html = "'Merge Projects' will allow two or more existing projects to be merged into a single project.<br />";
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
            $form->getElementById("merge_projects")->setPOST("projects");
            $form->getElementById("merge_acronym")->setPOST("acronym");
            $form->getElementById("merge_full_name")->setPOST("fullName");
            $form->getElementById("merge_status")->setPOST("status");
            $form->getElementById("merge_type")->setPOST("type");
            $form->getElementById("merge_effective")->setPOST("effective_date");
            $_POST['action'] = "MERGE";
            foreach($_POST['projects'] as $project){
                $_POST['project'] = $project;
                if(!APIRequest::doAction('EvolveProject', true)){
                    return "There was an error Merging the Projects";
                }
            }
            $form->reset();
        }
        else{
            return "The Projects were not merged";
        }
    }
}    
    
?>
