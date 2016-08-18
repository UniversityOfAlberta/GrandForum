<?php

class AllProductCountReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $start = $this->getAttr("start", REPORTING_CYCLE_START);
        $end = $this->getAttr("end", REPORTING_CYCLE_END);
        $uni = $this->getAttr("institution", "");
        $unis = array($uni);
	if($uni == "All"){
	    $allPaper = Paper::getAllPapersDuring('all',$category,'all',$start,$end, false, true);
	}
	else{
            $allPaper = Paper::getAllPapersByInstitutionDuring($category,$start,$end,true, $unis);
	}
        $tuple = self::createTuple();
        $tuple['person_id'] = count($allPaper);
        $data[] = $tuple;
        return $data;
    }
}

?>
