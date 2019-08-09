<?php

class ElseIfReportItemSet extends IfReportItemSet {
    
    function checkCondition(){
        $prev = $this->getPrev();
        if($prev instanceof IfReportItemSet){
            if(!$this->getPrev()->checkCondition()){
                $cond = $this->getAttr("if", '');
                return ($cond == "1");
            }
        }
        return false;
    }
    
}

?>
