<?php

class ProjectMilestonesROTab extends ProjectMilestonesTab {

    function ProjectMilestonesROTab($project){
        parent::ProjectMilestonesTab($project, array('edit' => false));
    }
    
    function canEdit(){
        return false;
    }

}    
    
?>
