<?php

class IsPLReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $me = Person::newFromWgUser();
        $not = (strtolower($this->getAttr("not", "false")) == "true");
        if(($me->leadershipOf($this->getReport()->project) && !$not) ||
           (!$me->leadershipOf($this->getReport()->project) && $not)){
            $tuple = self::createTuple();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
