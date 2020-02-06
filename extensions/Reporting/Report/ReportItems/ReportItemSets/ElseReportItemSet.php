<?php

class ElseReportItemSet extends IfReportItemSet {
    
    function checkCondition(){
        $prev = $this->getPrev();
        if($prev instanceof IfReportItemSet || $prev instanceof IfReportItem){
            return !$this->getPrev()->checkCondition();
        }
        return false;
    }

}

?>
