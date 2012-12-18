<?php

class PNIMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        
        $subs = $person->getEvaluatePNIs();
        
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                $tuple['person_id'] = $sub->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
