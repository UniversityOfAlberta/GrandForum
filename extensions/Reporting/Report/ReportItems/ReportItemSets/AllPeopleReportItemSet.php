<?php

class AllPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $roles = explode(",",$this->getAttr("roles", ""));
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $randomize = (strtolower($this->getAttr("randomize", "false")) == "true");
        $allPeople = Person::getAllPeople();
        foreach($allPeople as $person){
            $found = true;
            foreach($roles as $role){
                if($role != "" && !$person->isRoleDuring($role, $start, $end)){
                    $found = false;
                    break;
                }
            }
            if($found){
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $data[] = $tuple;
            }
        }
        if($randomize){
            shuffle($data);
        }
        return $data;
    }
}

?>
