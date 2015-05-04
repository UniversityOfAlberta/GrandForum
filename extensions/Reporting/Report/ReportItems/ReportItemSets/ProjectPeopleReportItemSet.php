<?php

class ProjectPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        $proj_id = $this->projectId;
        if($proj != null){
            $members = $proj->getAllPeopleDuring(NI, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL);
            $alreadySeen = array();
            foreach($members as $m){
                if(isset($alreadySeen[$m->getId()])){
                    continue;
                }
                $alreadySeen[$m->getId()] = true;
                $tuple = self::createTuple();
                $tuple['person_id'] = $m->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }
}

?>
