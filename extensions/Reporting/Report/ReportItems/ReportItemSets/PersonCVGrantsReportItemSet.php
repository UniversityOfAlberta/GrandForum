<?php

class PersonCVGrantsReportItemSet extends ReportItemSet {
    
    function getData(){
        $phase = $this->getAttr("phase");
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', CYCLE_START);
        $end = $this->getAttr('end', CYCLE_END);
        $grants = $person->getGrantsBetween($start, $end, true);
        $limit = $this->getAttr("limit", "");
        if(is_array($grants)){
            foreach($grants as $grant){
                $tuple = self::createTuple();
                $tuple['product_id'] = $grant->id;
                $data[] = $tuple;
            }
        }
        if($limit > 0){
            $data = array_slice($data,0,$limit);
        }
        return $data;
    }

}

?>
