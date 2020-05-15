<?php

class ProjectLeadersReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromHistoricId($this->projectId);
        if($proj != null){
            $leaders = $proj->getLeaders();
            $alreadySeen = array();
            foreach($leaders as $m){
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
