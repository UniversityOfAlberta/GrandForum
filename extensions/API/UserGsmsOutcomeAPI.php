<?php

class UserGsmsOutcomeAPI extends API{

    function UserGsmsOutcomeAPI(){
    }

    function processParams($params){
        if(isset($_POST['gpa']) && $_POST['gpa'] != ""){
            $_POST['gpa'] = str_replace("'", "&#39;", $_POST['gpa']);
        }
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
        $data = DBFunctions::select(array('grand_gsms'),
                            array('user_id'),
                            array('user_id'=> EQ($person->getId())));
        if(count($data)==0){
            DBFunctions::insert('grand_gsms',
                                array('user_id' => $person->getId()),
                                true);
        }
                  $gsms_sheet = GsmsData::newFromUserId($person->getId());
                              $gsms_sheet->funding_note = trim($_POST['funding_note']);
                              $gsms_sheet->department_decision = trim($_POST['department_decision']);
                              $gsms_sheet->fgsr_decision = $_POST['fgsr_decision'];
                              $gsms_sheet->decision_response = trim($_POST['decision_response']);

            $gsms_sheet->update();

        $person->getUser()->invalidateCache();
        if(!$noEcho){
            echo "User's GPA updated \n";
        }
        }

        function isLoginRequired(){
                return true;
        }
}
?>
