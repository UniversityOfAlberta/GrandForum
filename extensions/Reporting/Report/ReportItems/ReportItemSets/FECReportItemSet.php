<?php

class FECReportItemSet extends ReportItemSet {
    
    static $people = null;
    static $vdeans = array();
    static $hrs = array();
    
    static function generateFECCache(){
        if(self::$people == null){
            self::$people = Person::filterFaculty(array_merge(Person::getAllPeople('FEC')));
        }
    }
    
    function getData(){
        $data = array();
        $includeVDean = (strtolower($this->getAttr("includeVDean", "false")) == "true");
        $includeHR = (strtolower($this->getAttr("includeHR", "false")) == "true");
        $excludeMe = (strtolower($this->getAttr("excludeMe", "false")) == "true");
        self::generateFECCache();
        foreach(self::$people as $person){
            if($excludeMe && $person->isMe()){
                continue;
            }
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $data[] = $tuple;
        }
        if($includeVDean){
            if(empty(self::$vdeans)){
                self::$vdeans = Person::getAllPeople(VDEAN);
            }
            foreach(self::$vdeans as $person){
                if($excludeMe && $person->isMe()){
                    continue;
                }
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $data[] = $tuple;
            }
        }
        if($includeHR){
            if(empty(self::$hrs)){
                self::$hrs = Person::getAllPeople(HR);
            }
            foreach(self::$hrs as $person){
                if($excludeMe && $person->isMe()){
                    continue;
                }
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }
}

?>
