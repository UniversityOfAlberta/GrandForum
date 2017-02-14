<?php

class ArrayReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $array = @unserialize($this->getAttr("array"));
        $index = $this->getAttr("index", null);
        if($index != null && isset($array[$index])){
            $array = $array[$index];
        }
        if(is_array($array)){
            foreach($array as $key => $el){
                if($el == null || $el == ""){
                    continue;
                }
                $tuple = self::createTuple();
                $tuple['extra'] = $el;
                $data[$key] = $tuple;
            }
        }
        return $data;
    }

}

?>
