<?php

class PersonFutureProjectReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        if($this->getReport()->topProjectOnly){
            $projects = array($this->getReport()->project);
        }
        else{
            $projects = $person->getProjectsDuring(($this->getReport()->year+1).REPORTING_CYCLE_START_MONTH, 
                                                   ($this->getReport()->year+1).REPORTING_CYCLE_END_MONTH);
        }
        if(is_array($projects)){
            foreach($projects as $proj){
                if($proj->getPhase() == 2){
                    $tuple = self::createTuple();
                    $tuple['project_id'] = $proj->getId();
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
