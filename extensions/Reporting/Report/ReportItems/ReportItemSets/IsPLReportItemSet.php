<?php

class IsPLReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $me = Person::newFromWgUser();
        $not = (strtolower($this->getAttr("not", "false")) == "true");
        if(($me->isRole(PL, $this->getReport()->project) && !$not) ||
           (!$me->isRole(PL, $this->getReport()->project) && $not)){
            $tuple = self::createTuple();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
