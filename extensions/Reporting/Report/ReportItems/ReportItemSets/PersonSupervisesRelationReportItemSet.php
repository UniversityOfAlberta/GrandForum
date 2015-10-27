<?php

class PersonSupervisesRelationReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        foreach($person->getRelations("Supervises", REPORTING_CYCLE_START, REPORTING_CYCLE_END) as $relation){
            $tuple = self::createTuple();
    	    $tuple['project_id'] = $relation->getId();
            $data[] = $tuple;	
	}
	return $data;	
    }   
    
}

?>
