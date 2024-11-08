<?php

class ElseIfReportItemSet extends IfReportItemSet {
    
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
        if($ret){
            if($this->cond === null){
                $this->cond = $this->getAttr("if", '');
            }
            return ($this->cond == "1");
        }
        return false;
    }
    
}

?>
