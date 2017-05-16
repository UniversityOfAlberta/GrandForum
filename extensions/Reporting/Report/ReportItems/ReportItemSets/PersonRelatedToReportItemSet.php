<?php

class PersonRelatedToReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->getAttr("personId", $this->personId));
        $relation = $this->getAttr('relation');
        foreach($person->getPeopleRelatedTo($relation) as $hqp){
            $tuple = self::createTuple();
            $tuple['person_id'] = $hqp->getId();
            $data[] = $tuple;
        }
        return $data;
    }   
    
}

?>
