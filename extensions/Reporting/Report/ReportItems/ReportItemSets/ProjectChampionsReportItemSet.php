<?php

class ProjectChampionsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $project = Project::newFromHistoricId($this->projectId);
        $projects = array();
        if($project != null){
            $projects[] = $project;
        }
        $alreadySeen = array();
        foreach($projects as $proj){
            $champs = $proj->getChampionsOn((REPORTING_YEAR+1).REPORTING_RMC_MEETING_MONTH);
            foreach($champs as $c){
                if(isset($alreadySeen[$c['user']->getId()])){
                    continue;
                }
                $alreadySeen[$c['user']->getId()] = true; 
                $tuple = self::createTuple();
                $tuple['person_id'] = $c['user']->getId();
                $data[] = $tuple;
            }
        }
        
        return $data;
    }
}

?>
