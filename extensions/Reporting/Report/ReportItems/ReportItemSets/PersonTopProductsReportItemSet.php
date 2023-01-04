<?php

class PersonTopProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $products = $person->getTopProducts();
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
