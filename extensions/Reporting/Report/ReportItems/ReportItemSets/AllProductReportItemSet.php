<?php

class AllProductReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
	$order = $this->getAttr("order", "");
        $allPaper = Paper::getAllPapersDuring('all',$category,'all',$start,$end, false, true, $order);
        foreach($allPaper as $paper){
            $tuple = self::createTuple();
            $tuple['person_id'] = $paper->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
