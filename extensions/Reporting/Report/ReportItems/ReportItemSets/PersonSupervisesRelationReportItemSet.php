<?php

class PersonSupervisesRelationReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        foreach($person->getRelationsDuring("Supervises", $start, $end) as $relation){
            $tuple = self::createTuple();
    	    $tuple['project_id'] = $relation->getId();
            $data[] = $tuple;	
	}
	return $data;	
    }   
    
}

?>
