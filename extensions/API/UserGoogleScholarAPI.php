<?php

class UserGoogleScholarAPI extends API{

    function UserGoogleScholarAPI(){
        $this->addPOST("googleScholarUrl", true, "The url of google scholar profile", "http://www.mywebsite.com");
    }

    function processParams($params){
        if(isset($_POST['googleScholarUrl']) && $_POST['googleScholarUrl'] != ""){
            $_POST['googleScholarUrl'] = str_replace("'", "&#39;", $_POST['googleScholarUrl']);
        }
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        DBFunctions::update('mw_user',
                            array('google_scholar_url' => $_POST['googleScholarUrl']),
                            array('user_id' => EQ($person->getId())));
        Cache::delete("idsCache_{$person->getId()}");
        if(!$noEcho){
            echo "Google Scholar Url added\n";
        }
    }

    function isLoginRequired(){
                return true;
    }
}
?>
