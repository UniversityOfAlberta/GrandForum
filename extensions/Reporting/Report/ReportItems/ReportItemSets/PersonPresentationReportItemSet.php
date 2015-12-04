<?php
class PersonPresentationReportItemSet extends ReportItemSet {
    function getData(){
        $phase = $this->getAttr("phase");
        $data = array();
        $person = Person::newFromId($this->personId);
        $presentations= $person->getPresentations();
        if(is_array($presentations)){
            foreach($presentations as $presentation){
                $tuple = self::createTuple();
                $tuple['product_id'] = $presentation->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }
}
?>
