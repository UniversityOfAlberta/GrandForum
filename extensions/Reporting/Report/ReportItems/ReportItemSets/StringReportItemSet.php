<?php

class StringReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $array = explode("|", $this->getAttr("array"));
        $labels = ($this->getAttr("labels") != "") ? explode("|", $this->getAttr("labels")) : array();
        foreach($array as $key => $el){
            if($el == null || $el == ""){
                continue;
            }
            $tuple = self::createTuple();
            if(isset($labels[$key])){
                // Label found
                $tuple['extra'] = array('label' => $labels[$key], 'value' => $el);
            }
            else{
                $tuple['extra'] = $el;
            }
            $data[$key] = $tuple;
        }
        return $data;
    }

}

?>
