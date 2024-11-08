<?php

class ElseReportItem extends IfReportItem {
    
    function checkCondition(){
        $ret = true;
        $prev = $this->getPrev();
        while($prev instanceof IfReportItemSet || $prev instanceof IfReportItem){
            $cond = $prev->checkCondition();
            $ret = $ret && !$cond;
            if(get_class($prev) == "IfReportItemSet" || get_class($prev) == "IfReportItem"){
                break;
            }
            $prev = $prev->getPrev();
        }
        return $ret;
    }

}

?>
