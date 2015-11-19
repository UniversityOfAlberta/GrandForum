<?php

class FECReportItemSet extends ReportItemSet {
    
    function getData(){
        $id = $this->getAttr("userid");
	$data = array();
        $tuple = self::createTuple();
	$tuple['person_id'] = $id;
        $data[] = $tuple;
        return $data;
    }
}

?>
