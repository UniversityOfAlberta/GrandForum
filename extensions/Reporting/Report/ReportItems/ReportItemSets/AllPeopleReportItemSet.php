<?php

class AllPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $roles = explode(",",$this->getAttr("roles", ""));
        $subRoles = explode(",", $this->getAttr("subRoles", ""));
        $sort_reversed = $this->getAttr("sort_reversed", "false");
        $dept = $this->getAttr("department", "false");
        $start = $this->getAttr("start", CYCLE_START);
        $end = $this->getAttr("end", CYCLE_END);
        $allPeople = Person::getAllPeople();
        if($sort_reversed == "true"){
            usort($allPeople, function($a, $b){
                $name_a = explode(".",$a->name);
                $name_b = explode(".",$b->name);
                if(count($name_a) >1 && count($name_b) >1){
                    return strcmp($name_a[1], $name_b[1]);
                }
                return 0;
            });
        }
        foreach($allPeople as $person){
            $person->getUniversity();
            $found = true;
            foreach($roles as $role){
                if($role != "" && !$person->isRoleDuring($role, $start, $end)){
                    $found = false;
                    break;
                }
            }
            foreach($subRoles as $role){
                if($role != "" && !$person->isSubRole($role)){
                    $found = false;
                    break;
                }
            }
            if($found){
                $tuple = self::createTuple();
                if($dept != "false"){
                    $university = $person->university;
                    if($university['department'] == $dept){
                        $tuple['person_id'] = $person->getId();
                        $data[] = $tuple;
                    }
                }
                else{
                    $tuple['person_id'] = $person->getId();
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }
}

?>
