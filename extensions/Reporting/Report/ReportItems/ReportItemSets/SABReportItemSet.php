<?php

class SABReportItemSet extends ReportItemSet {
    
    function getData(){
        // Returns the array of SAB for that year
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $evaluators = $proj->getEvaluators($this->getReport()->year, 'SAB');
            foreach($evaluators as $e){
                $tuple = self::createTuple();
                $tuple['person_id'] = $e->getId();
                $data[$e->getReversedName()] = $tuple;
            }
            ksort($data);
        }
        return $data;
    }
}

?>
