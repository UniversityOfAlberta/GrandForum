<?php

class UserOrcIdAPI extends API{

    function UserOrcIdAPI(){
        $this->addPOST("orcId", true, "The ORCID of user", "");
    }

    function processParams($params){
        if(isset($_POST['orcId']) && $_POST['orcId'] != ""){
            $_POST['orcId'] = str_replace("'", "&#39;", $_POST['orcId']);
        }
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('orcid' => $_POST['orcId']),
                            array('user_id' => EQ($person->getId())));
        if(!$noEcho){
            echo "ORCID added\n";
        }
    }

    function isLoginRequired(){
        return true;
    }
}
?>
