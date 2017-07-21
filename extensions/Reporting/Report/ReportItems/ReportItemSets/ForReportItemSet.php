<?php

class ForReportItemSet extends ArrayReportItemSet {
    
    function getData(){
        $from = intval($this->getAttr("from", '0'));
        $to = intval($this->getAttr("to", '1'));
        $data = array();
        for($i=$from; $i<=$to; $i++){
            $tuple = self::createTuple();
            $tuple['extra'] = "$i";
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
