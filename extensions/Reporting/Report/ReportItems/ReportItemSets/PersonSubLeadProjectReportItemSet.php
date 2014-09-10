<?php

class PersonSubLeadProjectReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $projects = array();
        $leadership = $person->leadership();
        if(count($leadership) > 0){
            foreach($leadership as $lead){
                if($lead->isSubProject()){
                    if($this->getReport()->topProjectOnly && $lead->getId() == $this->projectId){
                        continue;
                    }
                    $parent = $lead->getParent();
                    $projects[$parent->getId()] = $parent;
                }
            }
        }
        if(is_array($projects)){
            foreach($projects as $proj){
                if(!$proj->isSubProject()){
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
