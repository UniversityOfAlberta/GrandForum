<?php

class PersonProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $products = $person->getPapersAuthored('all', REPORTING_CYCLE_START, REPORTING_CYCLE_END, true);
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
