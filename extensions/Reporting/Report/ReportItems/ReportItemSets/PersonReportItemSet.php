<?php

class PersonReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $person = null;
        
        $userName = $this->getAttr('userName', '');
        $userId   = $this->getAttr('userId'  , '');
        
        if($userName != '' && $userId == ''){
            $person = Person::newFromNameLike($userName);
        }
        else if($userId != '' && $userName == ''){
            $person = Person::newFromId($userId);
        }
        if($person != null){
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $data[] = $tuple;
        }

        return $data;
    }
}

?>
