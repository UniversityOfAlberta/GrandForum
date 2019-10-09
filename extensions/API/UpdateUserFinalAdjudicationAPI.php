<?php

class UpdateUserFinalAdjudicationAPI extends API{

    function UpdateUserFinalAdjudicationAPI(){
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        if(!$me->isRoleAtLeast(MANAGER)){
            return;
        }   
        $person = Person::newFromName($_POST['user_name']);
        $data = DBFunctions::select(array('grand_gsms'),
                                    array('user_id'),
                                    array('user_id'=> EQ($person->getId())));
        if(count($data)==0){
            DBFunctions::insert('grand_gsms',
                                array('user_id' => $person->getId()),
                                true);
        }
        $gsms = GsmsData::newFromUserId($person->getId());
        $gsms->setAdditional('funding_note', trim($_POST['funding_note']));
        $gsms->setAdditional('department_decision', trim($_POST['department_decision']));
        $gsms->setAdditional('fgsr_decision', $_POST['fgsr_decision']);
        $gsms->setAdditional('decision_response', trim($_POST['decision_response']));
        $gsms->update();

        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "User's Final Adjudication updated \n";
        }
    }

    function isLoginRequired(){
            return true;
    }
}
?>
