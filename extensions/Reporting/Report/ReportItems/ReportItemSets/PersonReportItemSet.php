<?php

class PersonReportItemSet extends ReportItemSet {
    
    function getData(){
        global $wgUser;
        $wgUserBefore = $wgUser;
        $wgUser = User::newFromId(1); // This is needed for EliteLetters
        $data = array();
        $person = null;
        
        $userName = $this->getAttr('userName', '');
        $userId   = $this->getAttr('userId'  , '');
        
        if($userName != '' && $userId == ''){
            $person = Person::newFromNameLIke($userName);
        }
        else if($userId != '' && $userName == ''){
            $person = Person::newFromId($userId);
        }
        if($person != null){
            $tuple = self::createTuple();
            $tuple['person_id'] = $person->getId();
            $data[] = $tuple;
        }
        $wgUser = $wgUserBefore;
        return $data;
    }
}

?>
