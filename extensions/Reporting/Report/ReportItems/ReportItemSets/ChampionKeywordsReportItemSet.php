<?php

class ChampionKeywordsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $projects = $person->getProjectsDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        $alreadyDone = array();
        if(is_array($projects)){
            foreach($projects as $proj){
                $tuple = self::createTuple();
                $tuple['person_id'] = 0;
                $tuple['project_id'] = $proj->getId();
                $data[] = $tuple;
                $people = array_merge($proj->getAllPeopleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                                      $proj->getAllPeopleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END));
                foreach($people as $person){
                    if(!isset($alreadyDone[$person->getId()])){
                        $tuple = self::createTuple();
                        $tuple['person_id'] = $person->getId();
                        $tuple['project_id'] = 0;
                        $data[] = $tuple;
                        $alreadyDone[$person->getId()] = true;
                    }
                }
            }
        }
        return $data;
    }

}

?>
