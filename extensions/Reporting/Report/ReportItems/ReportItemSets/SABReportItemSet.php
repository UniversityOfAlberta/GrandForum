<?php

class SABReportItemSet extends ReportItemSet {
    
    function getData(){
        $me = Person::newFromWgUser();
        // Returns the array of SAB for that year
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $evaluators = $proj->getEvaluators($this->getReport()->year, 'SAB');
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
