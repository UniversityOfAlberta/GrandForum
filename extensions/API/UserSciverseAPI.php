<?php

class UserSciverseAPI extends API{

    function UserSciverseAPI(){
        $this->addPOST("sciverseId", true, "The sciverse id of user", "http://www.mywebsite.com");
    }

    function processParams($params){
        if(isset($_POST['sciverseId']) && $_POST['sciverseId'] != ""){
            $_POST['sciverseId'] = str_replace("'", "&#39;", $_POST['sciverseId']);
        }
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('sciverse_id' => $_POST['sciverseId']),
                            array('user_id' => EQ($person->getId())));
        Cache::delete("idsCache_{$person->getId()}");
        if(!$noEcho){
            echo "Sciverse Id added\n";
        }
    }

    function isLoginRequired(){
                return true;
    }
}
?>
