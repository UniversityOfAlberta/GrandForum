<?php

class ProjectAllPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $members = $proj->getAllPeopleDuring();
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
