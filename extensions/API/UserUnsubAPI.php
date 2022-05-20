<?php

class UserUnsubAPI extends API{

    function __construct(){
        $this->addGET("code", false, "Hashed code of user", "");
    }

    function processParams($params){
    }

	function doAction($noEcho=false){
	    global $wgOut, $wgMessage;
        $people = Person::getAllPeople();
        foreach($people as $person){
            if(@$_GET['code'] == hash('sha256', $person->getId()."_".$person->getRegistration())){
                // Person found
                $data = DBFunctions::select(array('wikidev_unsubs'),
                                            array('*'),
                                            array('user_id' => $person->getId(),
                                                  'project_id' => 0));
                if(count($data) == 0){
                    DBFunctions::insert('wikidev_unsubs',
                                        array('user_id' => $person->getId(),
                                              'project_id' => 0));
                    DBFunctions::commit();
                    $wgMessage->addSuccess("You have been unsubscribed from AVOID Frailty");
                }
                else{
                    $wgMessage->addSuccess("You are already unsubscribed from AVOID Frailty");
                }
                $wgOut->output();
                exit;
            }
        }
        // No Person found
        $wgMessage->addError("This is not a valid unsubscribe code");
        $wgOut->output();
        exit;
	}
	
	function isLoginRequired(){
		return false;
	}
}
?>
