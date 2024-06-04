<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();

        $person = Person::newFromId($this->personId);
        $startDate = $this->getAttr("start", REPORTING_CYCLE_START);
        $endDate = $this->getAttr("end", REPORTING_CYCLE_END);
        
        $hqpType = explode("|", $this->getAttr('hqpType', 'grad'));
        $positions = array();
        foreach($hqpType as $type){
            $positions = array_merge($positions, Person::$studentPositions[$type]);
        }
        
        $hqps = $person->getStudentInfo($positions, $startDate, $endDate);
        foreach($hqps as $row){
            $tuple = self::createTuple();
            $tuple['person_id'] = $row['hqp'];
            $data[$row['hqp']] = $tuple;
        }

        return array_values($data);
    }
}

?>
