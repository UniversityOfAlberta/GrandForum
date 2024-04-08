<?php

class StringReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $array = explode("|", $this->getAttr("array"));
        foreach($array as $key => $el){
            if($el == null || $el == ""){
                continue;
            }
            $tuple = self::createTuple();
            $tuple['extra'] = $el;
            $data[$key] = $tuple;
        }
        return $data;
    }

}

?>
