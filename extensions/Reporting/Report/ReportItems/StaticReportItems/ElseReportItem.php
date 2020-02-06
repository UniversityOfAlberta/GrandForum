<?php

class ElseReportItem extends IfReportItem {
    
    function checkCondition(){
        $prev = $this->getPrev();
        if($prev instanceof IfReportItemSet || $prev instanceof IfReportItem){
            return !$this->getPrev()->checkCondition();
        }
        return false;
    }

}

?>
