<?php

class OptionsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $options = @explode("|", $this->getAttr('options', ""));
        

        $i=1;
        foreach($options as $opt){
           
            $tuple = self::createTuple();
            $tuple['misc'] = array('options'=>$opt);
            $tuple['item_id'] = $i++;
            $data[] = $tuple;
            //print_r($tuple);
        }
        
        return $data;
    }

}

?>
