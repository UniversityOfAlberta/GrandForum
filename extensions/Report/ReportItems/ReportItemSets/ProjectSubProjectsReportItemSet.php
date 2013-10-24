<?php

class ProjectSubProjectsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $project = Project::newFromId($this->projectId);
        $projects = $project->getSubProjectsDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        if(is_array($projects)){
            foreach($projects as $proj){
                $tuple = self::createTuple();
                $tuple['project_id'] = $proj->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
