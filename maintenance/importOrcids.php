<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);
    
    $orcids = array_map("str_getcsv", file("orcids.csv"));
    
    foreach($orcids as $row){
        $person = Person::newFromNameLike(trim($row[1]));
        if($person == null){
            $exploded = explode(" ", trim($row[1]));
            $person = Person::newFromNameLike($exploded[0]." ".$exploded[count($exploded)-1]);
        }
        if($person instanceof FullPerson && $person->getOrcId() == ""){
            DBFunctions::update('mw_user',
                                array('orcid' => $row[0]),
                                array('user_id' => $person->getId()));
            echo "{$person->getName()}\n";
        }
    }

?>
