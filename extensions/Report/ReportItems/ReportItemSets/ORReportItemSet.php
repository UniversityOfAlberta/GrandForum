<?php

class ORReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $tuple = self::createTuple();
        $data[] = $tuple;
        return $data;
    }
    
    function getNComplete(){
        $nComplete = 0;
        $noFound = false;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            $nComplete = $item->getNComplete();
            if($nComplete >= 1){
                return 1;
            }
        }
        return 0;
    }
    
    function getNFields(){
        return 1;
    }
}

?>
