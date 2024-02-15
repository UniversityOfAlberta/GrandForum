<?php

class ForReportItemSet extends ArrayReportItemSet {
    
    function getData(){
        $data = array();
        $from = intval($this->getAttr("from", '0'));
        $to = intval($this->getAttr("to", '1'));
        $array = $this->getAttr("array", "");
        if($array != ""){
            $array = explode("|", $array);
            foreach($array as $val){
                $tuple = self::createTuple();
                $tuple['extra'] = "$val";
                $data[] = $tuple;
            }
        }
        else{
            if($from <= $to){
                for($i=$from; $i<=$to; $i++){
                    $tuple = self::createTuple();
                    $tuple['extra'] = "$i";
                    $data[] = $tuple;
                }
            }
            else{
                for($i=$from; $i>=$to; $i--){
                    $tuple = self::createTuple();
                    $tuple['extra'] = "$i";
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }
}

?>
