<?php

class PersonContributionsReportItemSet extends ReportItemSet {
    
    function getData(){
        $phase = $this->getAttr("phase");
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $contributions = $person->getContributionsBetween($start, $end);
        if(is_array($contributions)){
            foreach($contributions as $contribution){
                $tuple = self::createTuple();
                $tuple['project_id'] = $contribution->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
