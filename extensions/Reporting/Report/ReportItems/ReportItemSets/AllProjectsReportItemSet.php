<?php

class AllProjectsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $phase = ($this->getAttr("phase") != "") ? $this->getAttr("phase") : 0;
        if($this->getReport()->topProjectOnly){
            $projects = array($this->getReport()->project);
        }
        else{
            $projects = Project::getAllProjects();
        }
        foreach($projects as $project){
            if($project == null || ($phase != 0 && $project->getPhase() != $phase)){
                continue;
            }
            $tuple = self::createTuple();
            $tuple['project_id'] = $project->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
