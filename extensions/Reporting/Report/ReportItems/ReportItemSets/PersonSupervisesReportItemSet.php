<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();

        $person = Person::newFromId($this->personId);
        $startDate = $this->getAttr("start", REPORTING_CYCLE_START);
        $endDate = $this->getAttr("end", REPORTING_CYCLE_END);
        
        $hqpType = $this->getAttr('hqpType', 'grad');
        $hqps = $person->getStudentInfo(Person::$studentPositions[$hqpType], $startDate, $endDate);
        foreach($hqps as $row){
            $tuple = self::createTuple();
            $tuple['person_id'] = $row['hqp'];
            $data[] = $tuple;
        }

        return $data;
    }
}

?>
