<?php

class ANDReportItemSet extends ReportItemSet {
    
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
            $nFields = $item->getNFields();
            if($nComplete < $nFields){
                return 0;
            }
        }
        return 1;
    }
    
    function getNFields(){
        return 1;
    }
}

?>
