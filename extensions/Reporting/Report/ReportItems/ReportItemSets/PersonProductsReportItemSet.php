<?php

class PersonProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $me = Person::newFromWgUser();
        $person = Person::newFromId($this->personId);
        //$products = $person->getPapersAuthored($category, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL, true);
        $products = $person->getPapers($category, false, 'both', true, "Public");
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
