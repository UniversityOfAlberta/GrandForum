<?php

class DateReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $date = date('Y-m-d', time());
        $start = $this->getAttr('start');
        $end = $this->getAttr('end');
        if($date >= $start && $date <= $end){
            $tuple = self::createTuple();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
