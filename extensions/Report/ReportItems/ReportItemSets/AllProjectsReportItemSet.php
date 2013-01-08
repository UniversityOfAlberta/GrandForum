<?php

class AllProjectsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $projects = Project::getAllProjects();
        foreach($projects as $project){
            $tuple = self::createTuple();
            $tuple['project_id'] = $project->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
