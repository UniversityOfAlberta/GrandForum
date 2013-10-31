<?php

class ProjectChampionsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $champs = $proj->getChampionsDuring();
            $alreadySeen = array();
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
