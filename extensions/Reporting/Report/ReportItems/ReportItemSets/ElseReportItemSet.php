<?php

class ElseReportItemSet extends IfReportItemSet {
    
    function checkCondition(){
        $ret = true;
        $prev = $this->getPrev();
        while($prev instanceof IfReportItemSet || $prev instanceof IfReportItem){
            $cond = $prev->getAttr("if", '');
            $ret = $ret && !($cond == "1");
            $prev = $prev->getPrev();
        }
        return $ret;
    }

}

?>
