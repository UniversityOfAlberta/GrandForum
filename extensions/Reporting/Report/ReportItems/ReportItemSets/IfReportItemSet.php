<?php

class IfReportItemSet extends ReportItemSet {
    
    function checkCondition(){
        $cond = $this->getAttr("if", '');
        return ($cond == "1");
    }
    
    function getData(){
        $data = array();
        if($this->checkCondition()){
            $tuple = self::createTuple();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
