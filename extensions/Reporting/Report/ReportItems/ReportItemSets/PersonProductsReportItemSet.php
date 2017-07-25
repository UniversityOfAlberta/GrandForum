<?php

class PersonProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $start_date = $this->getAttr("start", REPORTING_CYCLE_START);
        $end_date = $this->getAttr("end",REPORTING_CYCLE_END_ACTUAL);
        $me = Person::newFromWgUser();
        $person = Person::newFromId($this->personId);
        $products = $person->getPapersAuthored($category, $start_date, $end_date, true);
        usort($products, function($a, $b){
            return (str_replace("0000-00-00", "9999-99-99", $a->getDate()) < str_replace("0000-00-00", "9999-99-99", $b->getDate())) ? 1 : -1;
        });
        //$products = $person->getPapers($category, false, 'both', true, "Public");
        if(is_array($products)){
            foreach($products as $prod){
                $tuple = self::createTuple();
                $tuple['product_id'] = $prod->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
