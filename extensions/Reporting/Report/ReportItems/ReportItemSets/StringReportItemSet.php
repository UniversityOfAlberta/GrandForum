<?php

class StringReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $array = explode("|", str_replace('\|', '&vert;', $this->getAttr("array")));
        $array2 = explode("|", str_replace('\|', '&vert;', $this->getAttr("array2")));
        $array3 = explode("|", str_replace('\|', '&vert;', $this->getAttr("array3")));
        $array4 = explode("|", str_replace('\|', '&vert;', $this->getAttr("array4")));
        $array5 = explode("|", str_replace('\|', '&vert;', $this->getAttr("array5")));
        $labels = ($this->getAttr("labels") != "") ? explode("|", $this->getAttr("labels")) : array();
        foreach($array as $key => $el){
            if($el == null || $el == ""){
                continue;
            }
            $tuple = self::createTuple();
            if(isset($labels[$key])){
                // Label found
                $tuple['extra'] = array('label' => $labels[$key], 
                                        'value' => trim(str_replace('&vert;', '|', $el)), 
                                        'value2' => @trim(str_replace('&vert;', '|', $array2[$key])), 
                                        'value3' => @trim(str_replace('&vert;', '|', $array3[$key])), 
                                        'value4' => @trim(str_replace('&vert;', '|', $array4[$key])), 
                                        'value5' => @trim(str_replace('&vert;', '|', $array5[$key])));
            }
            else if(count($array2) > 1 || count($array3) > 1 || count($array4) > 1 || count($array5) > 1){
                $tuple['extra'] = array('label' => "", 
                                        'value' => trim(str_replace('&vert;', '|', $el)), 
                                        'value2' => @trim(str_replace('&vert;', '|', $array2[$key])), 
                                        'value3' => @trim(str_replace('&vert;', '|', $array3[$key])), 
                                        'value4' => @trim(str_replace('&vert;', '|', $array4[$key])), 
                                        'value5' => @trim(str_replace('&vert;', '|', $array5[$key])));
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
