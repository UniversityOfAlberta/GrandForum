<?php

class ElseIfReportItem extends IfReportItem {
    
    function checkCondition(){
        $prev = $this->getPrev();
        if($prev instanceof IfReportItemSet || $prev instanceof IfReportItem){
            if(!$this->getPrev()->checkCondition()){
                $cond = $this->getAttr("if", '');
                return ($cond == "1");
            }
        }
        return false;
    }
    
}

?>
