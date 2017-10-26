<?php
    /**
    * @package GrandObjects
    */

class GsmsData extends BackboneModel{

    //static $cache = array();
    var $id;
    var $user_id;
    var $gsms_id;
    var $student_id;

//General data
    var $status;
    var $applicant_number;
    var $gender;
    var $date_of_birth;
    var $program_name;
    var $country_of_birth;
    var $country_of_citizenship;
    var $applicant_type;
    var $education_history;
    var $department;
    var $epl_test;
    var $epl_score;
    var $epl_listen;
    var $epl_write;
    var $epl_read;
    var $epl_speaking;
    var $additional = array(); 

//added just incase
    var $cs_app;
    var $academic_year;
    var $term;
    var $subplan_name;
    var $program;
    var $degree_code;
    var $admission_program_name;
    var $submitted_date;
    var $folder;
    var $department_gpa;
    var $department_gpa_scale;
    var $department_normalized_gpa;
    var $fgsr_gpa;
    var $fgsr_gpa_scale;
    var $fgsr_normalized_gpa;
    var $funding_note;
    var $department_decision;
    var $fgsr_decision;
    var $decision_response;
    var $general_notes;


//Specific for CS
    var $areas;
    var $supervisors;
    var $scholarships;
    var $gpa_normalized;
    var $gre_verbal;
    var $gre_quantitative;
    var $gre_analytical; 
    var $gre_cs;

    function GsmsData($data){
        global $config;
        if(count($data) > 0){
            $this->id = $data[0]['id'];
	    $this->user_id = $data[0]['user_id'];
            $this->gender = $data[0]['gender'];
            $this->gsms_id = $data[0]['gsms_id'];
            $this->applicant_number = $data[0]['applicant_number'];
            $this->date_of_birth = $data[0]['date_of_birth'];
            $this->program_name = $data[0]['program_name'];
            $this->country_of_birth = $data[0]['country_of_birth'];
            $this->country_of_citizenship = $data[0]['country_of_citizenship'];
            $this->applicant_type = $data[0]['applicant_type'];
            $this->education_history = $data[0]['education_history'];
            $this->department = $data[0]['department'];
            $this->additional = unserialize($data[0]['additional']);
        }
    }

    /**
    * Returns a new GsmsData from the given id
    * @param integer $id The id of the course
    * @return Course The Course with the given id. If no
    * course exists with that id, it will return an empty course.
    */
    static function newFromUserId($id){
        $data = DBFunctions::select(array('grand_gsms'),
                                    array('*'),
                                    array('user_id' => EQ($id)));
        $info_sheet = new GsmsData($data, $id);
        return $info_sheet;
    }

    static function getAllVisibleGsms(){
        global $wgRoleValues;
        $gsms_array = array();
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(EVALUATOR)){
            $data = DBFunctions::select(array('grand_gsms'),
                                        array('id'));
        }
        if(count($data) >0){
            foreach($data as $gsms){
                $gsms_array[] = GsmsData::newFromId($gsms['id']);
            }
        }
        return $gsms_array;
    }
   
  /**
   * newFromId Returns an Gsms object from a given id
   * @param $id
   * @return $gsms Gsms object
   */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_gsms'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $gsms = new GsmsData($data);
        return $gsms;
    }

    /**
     * Returns True if the course is saved correctly to the course table in the database
     * @return boolean True if the database accepted the new course
    */
    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
                DBFunctions::insert('grand_person_gsms',
                                    array('`user_id`' => $this->user_id,
                                          '`gpa60`' => $this->gpa60,
                                          '`gpafull`' => $this->gpafull,
                                          '`gpafull_credits`' => $this->gpafull_credits,
                                          '`gpafull2`' => $this->gpafull2,
					  '`gpafull_credits2`' => $this->gpafull_credits2,
					  '`notes`' => $this->notes,
					  '`anatomy`' =>$this->anatomy,
					  '`stats`' => $this->stats,
					  '`degree`' => $this->institution,
					  '`failures`' => $this->failures,
					  '`withdrawals`' => $this->withdrawals,
					  '`canadian`' => $this->canadian,
					  '`international`' => $this->international,
					  '`indigenous`' => $this->indigenous,
					  '`saskatchewan`' => $this->saskatchewan,
					  '`degrees`' => serialize($this->degrees)));
        }
    }

    /**
     * Returns True if the course is updated correctly to the course table in the database
     * @return boolean True if the database accepted the updated course
    */
    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
                DBFunctions::update('grand_person_gsms',
                                    array('`gpa60`' => $this->gpa60,
                                          '`gpafull`' => $this->gpafull,
                                          '`gpafull_credits`' => $this->gpafull_credits,
                                          '`gpafull2`' => $this->gpafull2,
                                          '`gpafull_credits2`' => $this->gpafull_credits2,
                                          '`notes`' => $this->notes,
                                          '`anatomy`' =>$this->anatomy,
                                          '`stats`' => $this->stats,
                                          '`degree`' => $this->institution,
                                          '`failures`' => $this->failures,
                                          '`withdrawals`' => $this->withdrawals,
                                          '`canadian`' => $this->canadian,
                                          '`international`' => $this->international,
                                          '`indigenous`' => $this->indigenous,
                                          '`saskatchewan`' => $this->saskatchewan,
                                          '`degrees`' => serialize($this->degrees)),
				     array('user_id' => EQ($this->user_id)));
        }
	return true;
    }

    /**
     * Returns an array of this object
     * @return array of object
    */
    function toArray(){
        global $wgUser, $config;
        //if(!$wgUser->isLoggedIn()){
          //  return array();
       // }
	$student = Person::newFromId($this->user_id);
        $student_data = array('id' => $student->getId(),
                        'name' => $student->getReversedName(),
                        'url' => $student->getUrl(),
                        'email' => $student->getEmail());
        $sop = SOP::newFromUserId($this->user_id);
        $json = array('user_id' =>$this->user_id,
                  'student_data' => $student_data,
                  'gsms_id' => $this->gsms_id,
                  'student_id' => $this->student_id,
 	          'applicant_number' => $this->applicant_number,
                  'gender' => $this->gender,
                  'date_of_birth' => $this->date_of_birth,
                  'program_name' => $this->program_name,
                  'country_of_birth' => $this->country_of_birth,
                  'country_of_citizenship' => $this->country_of_citizenship,
                  'applicant_type' => $this->applicant_type,
                  'education_history' => $this->education_history,
                  'department' => $this->department,
                  'epl_test' => $this->epl_test,
                  'epl_score' => $this->epl_score,
                  'epl_listen' => $this->epl_listen,
                  'epl_write' => $this->epl_write,
                  'epl_read' => $this->epl_read,
                  'epl_speaking' => $this->epl_speaking,
                  'additional' => $this->additional);

        if($config->getValue('networkName') == 'GARS'){
          //sop information needed in table
            $json['sop_id'] = $sop->getId();
            $json['sop_url'] = $sop->getUrl();
            $json['annotations'] = $sop->annotations;

          //adding reviewers array so can have on overview table
            $reviewers = array();
            $reviewer_array = $student->getEvaluators(YEAR,"sop");
            foreach($reviewer_array as $reviewer){
                $person = $reviewer;
                $reviewers[] = array('id' => $person->getId(),
                                 'name' => $person->getNameForForms(),
                                 'url' => $person->getUrl(),
                                 'decision' => $sop->getAdmitResult($reviewer->getId()));
            }
            $json['reviewers'] = $reviewers;

           //adding nationality as one string
            $nationality = array();
            $nationality[] = ($this->additional['indigenous'] == "Yes") ? "Indigenous" : "";
            $nationality[] = ($this->additional['canadian'] == "Yes") ? "Canadian" : "";
            $nationality[] = ($this->additional['saskatchewan'] == "Yes") ? "Saskatchewan" : "";
            $nationality[] = ($this->additional['international'] == "Yes") ? "International" : "";

            $nationality_note = "";
            foreach($nationality as $note){
                if($note != ""){
                    $nationality_note .= $note.',<br />';
                }    
            }
            $json['nationality_note'] = $nationality_note;

            //adding decisions by boards
            $json['admit'] = $sop->getFinalAdmit();
        }

        return $json;

    }

    function delete(){
            //TODO:implement function
    }
    function exists(){
            //TODO:implement function
    }
    function getCacheId(){
            //TODO:implement function
    }
}

?>
