<?php

class SABReportItemSet extends ReportItemSet {
    
    function getData(){
        $me = Person::newFromWgUser();
        // Returns the array of SAB for that year
        $data = array();
        if($this->projectId != 0){
            $sub = Project::newFromId($this->projectId);
        }
        else{
            $sub = Person::newFromId($this->personId);
        }
        $subType = $this->getAttr('subType', 'SAB');
        if($sub != null){
            $evaluators = $sub->getEvaluators($this->getReport()->year, $subType);
            foreach($evaluators as $e){
                if($e->getId() != $me->getId()){
                    $tuple = self::createTuple();
                    $tuple['person_id'] = $e->getId();
                    $data[$e->getReversedName()] = $tuple;
                }
            }
            ksort($data);
        }
        return $data;
    }
}

?>
