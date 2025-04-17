<?php

class PersonContinuingGrantsReportItemSet extends PersonGrantsReportItemSet {
    
    function getData(){
        $data = array();
        $grants = $this->getGrants();
        $start = $this->getAttr('start', CYCLE_START);
        if(is_array($grants)){
            foreach($grants as $grant){
                if($grant->getStartDate() < $start){
                    $tuple = self::createTuple();
                    $tuple['product_id'] = $grant->id;
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
