<?php

class AllProductReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $uni = $this->getAttr("institution", "");
	    $order = $this->getAttr("order", "date");
	    $coll = $this->getAttr("coll", true);
        $unis = array($uni);
	if($coll == "false"){
	    $coll = false;
	}
	if($uni == "All"){
            $allPaper = Paper::getAllPapersDuring('all',$category,'all',$start,$end, false, true, $order, $coll);
	}
	else{
            $allPaper = Paper::getAllPapersByInstitutionDuring($category,$start,$end,true, $unis,$order);
	}
        foreach($allPaper as $paper){
            $tuple = self::createTuple();
            $tuple['person_id'] = $paper->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
