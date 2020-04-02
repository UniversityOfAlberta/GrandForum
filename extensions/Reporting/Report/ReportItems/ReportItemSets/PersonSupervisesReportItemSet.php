<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();

        $person = Person::newFromId($this->personId);
        $startDate = $this->getAttr("start", REPORTING_CYCLE_START);
        $endDate = $this->getAttr("end", REPORTING_CYCLE_END);
        
        $position = $this->getAttr('position', 'grad');
        
        $hqps = $person->getStudentInfo(Person::$studentPositions[$position], $startDate, $endDate);
        foreach($hqps as $row){
            $tuple = self::createTuple();
            $tuple['person_id'] = $row['hqp'];
            $data[] = $tuple;
        }

        return $data;
    }
}

?>
