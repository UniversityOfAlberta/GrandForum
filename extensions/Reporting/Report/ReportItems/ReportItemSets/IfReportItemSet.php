<?php

class IfReportItemSet extends ReportItemSet {
    
    var $cond = null;
    
    function checkCondition(){
        if($this->cond === null){
            $this->cond = $this->getAttr("if", '');
        }
        return ($this->cond == "1");
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
