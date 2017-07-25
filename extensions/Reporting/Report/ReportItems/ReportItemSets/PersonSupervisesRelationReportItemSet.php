<?php

class PersonSupervisesRelationReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $hqp = array_merge($person->getRelationsDuring(SUPERVISES, $start, $end), $person->getRelationsDuring(CO_SUPERVISES, $start, $end));
        usort($hqp, function($a, $b){
            if(str_replace("0000-00-00", "9999-99-99", $a->getEndDate()) != str_replace("0000-00-00", "9999-99-99", $b->getEndDate())){
                return (str_replace("0000-00-00", "9999-99-99", $a->getEndDate()) < str_replace("0000-00-00", "9999-99-99", $b->getEndDate())) ? 1 : -1;
            }
            return ($a->getUser2()->getReversedName() > $b->getUser2()->getReversedName()) ? 1 : -1;
        });
        foreach($hqp as $relation){
            $tuple = self::createTuple();
            $tuple['project_id'] = $relation->getId();
            $data[] = $tuple;
        }
        return $data;
    }    
}

?>
