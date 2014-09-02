<?php

class ProjectLeadersReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $leaders = array_merge($proj->getLeaders(), $proj->getCoLeaders());
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
