<?php

class SubProjectChampionsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $champions = array();
            $derivedChamps = array();
            foreach($proj->getChampionsDuring() as $champ){
                $champions[$champ['user']->getId()] = $champ;
            }
            if(!$proj->isSubProject()){
                foreach($proj->getSubProjects() as $sub){
                    foreach($sub->getChampionsDuring() as $champ){
                        if(!isset($champions[$champ['user']->getId()])){
                            if(!isset($derivedChamps[$champ['user']->getId()])){
                                $derivedChamps[$champ['user']->getId()] = $champ;
                            }
                        }
                    }
                }
            }
            $alreadySeen = array();
            foreach($derivedChamps as $c){
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
