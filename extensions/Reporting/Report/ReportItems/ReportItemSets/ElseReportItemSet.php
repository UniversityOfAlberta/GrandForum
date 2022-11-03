<?php

class ElseReportItemSet extends IfReportItemSet {
    
    function checkCondition(){
        $ret = true;
        $prev = $this->getPrev();
        while($prev instanceof IfReportItemSet || $prev instanceof IfReportItem){
            $cond = $prev->getAttr("if", '');
            $ret = $ret && !($cond == "1");
            if(get_class($prev) == "IfReportItemSet" || get_class($prev) == "IfReportItem"){
                break;
            }
            $prev = $prev->getPrev();
        }
        return $ret;
    }

}

?>
