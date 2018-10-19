<?php

class UserApplicantDataAPI extends API{

    function UserApplicantDataAPI(){
    }

    function processParams($params){
        if(isset($_POST['gpa']) && $_POST['gpa'] != ""){
            $_POST['gpa'] = str_replace("'", "&#39;", $_POST['gpa']);
        }
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
        $gsms_sheet = GsmsData::newFromUserId($person->getId());
        $gsms_sheet->gender = trim($_POST['gender']);
        $gsms_sheet->gsms_id = trim($_POST['gsms_id']);
        $gsms_sheet->student_id = $_POST['student_id'];
        $gsms_sheet->date_of_birth = trim($_POST['dob']);
        $gsms_sheet->country_of_birth = trim($_POST['country_birth']);
        $gsms_sheet->country_of_citizenship = trim($_POST['country_citizenship']);
        $gsms_sheet->applicant_type = trim($_POST['applicant_type']);
        $gsms_sheet->education_history = trim($_POST['education_history']);
        $gsms_sheet->program_name = trim($_POST['program_name']);
        $gsms_sheet->epl_test = trim($_POST['epl_test']);
        $gsms_sheet->epl_score = trim($_POST['epl_score']);
        $gsms_sheet->epl_listen = trim($_POST['listen']);
        $gsms_sheet->epl_write = trim($_POST['write']);
        $gsms_sheet->epl_read = trim($_POST['read']);
        $gsms_sheet->epl_speaking = trim($_POST['speaking']);
        $gsms_sheet->cs_app = trim($_POST['cs_app']);
        $gsms_sheet->academic_year = trim($_POST['academic_year']);
        $gsms_sheet->term = trim($_POST['term']);
        $gsms_sheet->subplan_name = trim($_POST['program_subplan']);
        $gsms_sheet->degree_code = trim($_POST['degree_code']);
        $gsms_sheet->admission_program_name = trim($_POST['admission_program']);
        $gsms_sheet->submitted_date = trim($_POST['submitted_date']);
        $gsms_sheet->folder = trim($_POST['folder']);
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
