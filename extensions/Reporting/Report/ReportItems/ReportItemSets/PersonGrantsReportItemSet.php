<?php

class PersonGrantsReportItemSet extends ReportItemSet {
    
    function getGrants(){
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        return $person->getGrantsBetween($start, $end);
    }
    
    function getData(){
        $data = array();
        $grants = $this->getGrants();
        if(is_array($grants)){
            foreach($grants as $grant){
                $tuple = self::createTuple();
                $tuple['product_id'] = $grant->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
