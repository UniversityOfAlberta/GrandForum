<?php

class FECReportItemSet extends ReportItemSet {
    
    static $people = null;
    static $vdeans = array();
    
    static function generateFECCache(){
        if(self::$people == null){
            self::$people = Person::filterFaculty(Person::getAllPeople('FEC'));
        }
    }
    
    function getData(){
        $data = array();
        $includeVDean = (strtolower($this->getAttr("includeVDean", "false")) == "true");
        self::generateFECCache();
        foreach(self::$people as $person){
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $data[] = $tuple;
        }
        if($includeVDean){
            if(empty(self::$vdeans)){
                self::$vdeans = Person::getAllPeople(VDEAN);
            }
            foreach(self::$vdeans as $person){
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }
}

?>
