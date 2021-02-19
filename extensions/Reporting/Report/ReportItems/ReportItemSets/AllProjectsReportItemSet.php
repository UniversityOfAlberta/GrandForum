<?php

class AllProjectsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $phase = $this->getAttr("phase", 0);
        $start = $this->getAttr("startDate", null);
        $end = $this->getAttr("endDate", null);
        $ever = (strtolower($this->getAttr("ever", "false")) == "true");
        if($this->getReport()->topProjectOnly){
            $projects = array($this->getReport()->project);
        }
        else{
            if($start != null && $end != null){
                $projects = Project::getAllProjectsDuring($start, $end);
            }
            else if($ever){
                $projects = Project::getAllProjectsEver();
            }
            else{
                $projects = Project::getAllProjects();
            }
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
