<?php
    /**
    * @package GrandObjects
    */

class GsmsData extends BackboneModel{

    static $cache = array();
    var $user_id;
//General data
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
    var $speaking; 

//Specific for CS
    var $areas;
    var $supervisors;
    var $scholarships;
    var $gpa_normalized;
    var $gre_verbal;
    var $gre_quantitative;
    var $gre_analytical; 
    var $gre_cs;

//specific for OT
    var $gpa60;
    var $gpafull;
    var $gpafull_credits;
    var $gpafull2;
    var $gpafull_credits2;
    var $notes;
    var $anatomy;
    var $stats;
    var $degree;
    var $institution;
    var $failures;
    var $withdrawals;
    var $canadian;
    var $international;
    var $indigenous;
    var $saskatchewan;
    var $degrees = array();


    // Constructor
    function GsmsData($data){
        if(count($data) > 0){
            $this->user_id = $data[0]['user_id'];
            $this->gpa60 = $data[0]['gpa60'];
            $this->gpafull = $data[0]['gpafull'];
            $this->gpafull_credits = $data[0]['gpafull_credits'];
            $this->gpafull2 = $data[0]['gpafull2'];
            $this->gpafull_credits2 = $data[0]['gpafull_credits2'];
            $this->notes = $data[0]['notes'];
            $this->anatomy = $data[0]['anatomy'];
            $this->stats = $data[0]['stats'];
            $this->degree = $data[0]['degree'];
            $this->institution = $data[0]['institution'];
            $this->failures = $data[0]['failures'];
            $this->withdrawals = $data[0]['withdrawals'];
            $this->canadian = $data[0]['canadian'];
            $this->international = $data[0]['international'];
            $this->indigenous = $data[0]['indigenous'];
            $this->saskatchewan = $data[0]['saskatchewan'];
            $this->degrees = unserialize($data[0]['degrees']);
        }
    }

    /**
    * Returns a new GsmsData from the given id
    * @param integer $id The id of the course
    * @return Course The Course with the given id. If no
    * course exists with that id, it will return an empty course.
    */
    static function newFromUserId($id){
        //check if exists in cache for easy access
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $data = DBFunctions::select(array('grand_person_gsms'),
                                    array('*'),
                                    array('user_id' => EQ($id)));
        $info_sheet = new GsmsData($data);
        return $info_sheet;
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
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return array();
        }
	$person = Person::newFromId($this->user_id);
        $json = array('user_id' =>$this->user_id,
		      'gpa60' => $this->gpa60,
                      'gpafull' => $this->gpafull,
                      'gpafull_credits' => $this->gpafull_credits,
                      'gpafull2' => $this->gpafull2,
                      'gpafull_credits2' => $this->gpafull_credits2,
                      'notes' => $this->notes,
                      'anatomy' => $this->anatomy,
                      'stats' => $this->stats,
                      'degree' => $this->degree,
                      'failures' => $this->failures,
                      'withdrawals' => $this->withdrawals,
                      'canadian' => $this->canadian,
                      'international' => $this->international,
                      'indigenous' => $this->indigenous,
                      'saskatchewan' => $this->saskatchewan,
                      'degrees' => $this->getDegrees());

        return $json;

    }

    function getDegrees(){
	if(count($this->degrees) == 0 || $this->degrees == ""){
	    return array();
	}
	return $this->degrees;
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
