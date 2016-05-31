<?php

class AllProductReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
	$order = $this->getAttr("order", "");
	$coll = $this->getAttr("coll", true);
	if($coll == "false"){
	    $coll = false;
	}
        $allPaper = Paper::getAllPapersDuring('all',$category,'all',$start,$end, false, true, $order, $coll);
        foreach($allPaper as $paper){
            $tuple = self::createTuple();
            $tuple['person_id'] = $paper->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
