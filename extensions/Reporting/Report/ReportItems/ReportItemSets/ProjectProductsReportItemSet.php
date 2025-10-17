<?php

class ProjectProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $project = Project::newFromHistoricId($this->projectId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $category = $this->getAttr("category", "all");
        $products = $project->getPapers($category, $start, $end);
        $peerReviewedOnly = (strtolower($this->getAttr("peerReviewedOnly", "false")) == "true");
        if(is_array($products)){
            foreach($products as $prod){
                if(!$peerReviewedOnly || $prod->getData('peer_reviewed') == "Yes"){
                    $tuple = self::createTuple();
                    $tuple['product_id'] = $prod->getId();
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
