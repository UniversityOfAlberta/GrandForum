<?php

class PersonProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $productType = explode("|", $this->getAttr("productType", ""));
        $peerReviewed = $this->getAttr("peerReviewed", "");
        $status = $this->getAttr("status", "");
        $start_date = $this->getAttr("start", REPORTING_CYCLE_START);
        $end_date = $this->getAttr("end", REPORTING_CYCLE_END_ACTUAL);
        $includeHQP = (strtolower($this->getAttr("includeHQP", "true")) == "true");
        $me = Person::newFromWgUser();
        $person = Person::newFromId($this->personId);
        $categories = explode("|", $category);
        $products = array();
        foreach($categories as $cat){
            $products = array_merge($products, $person->getPapersAuthored($cat, $start_date, $end_date, $includeHQP));
        }

        usort($products, function($a, $b){
            return (str_replace("0000-00-00", "9999-99-99", $a->getDate()) < str_replace("0000-00-00", "9999-99-99", $b->getDate())) ? 1 : -1;
        });
        //$products = $person->getPapers($category, false, 'both', true, "Public");
        if(is_array($products)){
            foreach($products as $prod){
                if((count($productType) == 0 || in_array($prod->getType(), $productType)) &&
                   ($peerReviewed == "" || ($prod->getData('peer_reviewed') == $peerReviewed)) &&
                   ($status == "" || ($prod->getStatus() == $status))){
                    $tuple = self::createTuple();
                    $tuple['product_id'] = $prod->id;
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
