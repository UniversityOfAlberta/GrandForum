<?php

class PersonSupervisesReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();

        $person = Person::newFromId($this->personId);
        $startDate = $this->getAttr("start", CYCLE_START);
        $endDate = $this->getAttr("end", CYCLE_END);
        
        $hqpType = explode("|", $this->getAttr('hqpType', 'grad'));
        $positions = array();
        foreach($hqpType as $type){
            $positions = array_merge($positions, Person::$studentPositions[$type]);
        }
        
        $hqps = $person->getStudentInfo($positions, $startDate, $endDate);
        foreach($hqps as $row){
            $tuple = self::createTuple();
            $tuple['person_id'] = $row['hqp'];
            $tuple['milestone_id'] = str_replace("/", "", 
                                     str_replace(" ", "", 
                                     str_replace("'", "", $row['position'])));
            $tuple['extra'] = $row;
            $data[$row['hqp'].$row['position']] = $tuple;
        }

        return array_values($data);
    }
}

?>
