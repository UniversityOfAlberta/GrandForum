<?php

class ProjectProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $project = Project::newFromHistoricId($this->projectId);
        $products = $project->getPapers('all', REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        if(is_array($products)){
            foreach($products as $prod){
                $tuple = self::createTuple();
                $tuple['product_id'] = $prod->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
