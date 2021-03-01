<?php
    /**
    * @package GrandObjects
    */

class GsmsData extends BackboneModel{

    var $id;
    var $user_id;
    var $gsms_id;
    var $ois_id;
    var $student_id;
    var $year;

//General data
    var $status;
    var $visible;
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
    var $gsms_url;

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

    function GsmsData($data){
        global $config;
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->user_id = $data[0]['user_id'];
            $this->status = $data[0]['status'];
            $this->visible = $data[0]['visible'];
            $this->gender = $data[0]['gender'];
            $this->student_id = $data[0]['student_id'];
            $this->gsms_id = $data[0]['gsms_id'];
            $this->ois_id = $data[0]['ois_id'];
            $this->applicant_number = $data[0]['applicant_number'];
            $dob = explode(" ", $data[0]['date_of_birth']);
            $this->date_of_birth = $dob[0];
            $program_name = nl2br(implode("\n", explode("; ", $data[0]['program_name'])));
            $this->program_name = $data[0]['program_name'];
            $this->country_of_birth = $data[0]['country_of_birth'];
            $this->country_of_citizenship = $data[0]['country_of_citizenship'];
            $this->applicant_type = $data[0]['applicant_type'];
            $this->education_history = $data[0]['education_history'];
            $this->department = $data[0]['department'];
            $this->epl_test = $data[0]['epl_test'];
            $this->epl_score = $data[0]['epl_score'];
            $this->epl_listen = $data[0]['epl_listen'];
            $this->epl_write = $data[0]['epl_write'];
            $this->epl_read = $data[0]['epl_read'];
            $this->epl_speaking = $data[0]['epl_speaking'];
            $this->additional = unserialize($data[0]['additional']);
            $this->cs_app = $data[0]['cs_app'];
            $this->academic_year = $data[0]['academic_year'];
            $this->term = $data[0]['term'];
            $this->subplan_name = $data[0]['subplan_name'];
            $this->program = $data[0]['program'];
            $this->degree_code = $data[0]['degree_code'];
            $this->admission_program_name = $data[0]['admission_program_name'];
            $sub = explode(" ",$data[0]['submitted_date']);
            $this->submitted_date = $sub[0];
            $this->folder = $data[0]['folder']; 
            $this->department_gpa = $data[0]['department_gpa'];
            $this->department_gpa_scale = $data[0]['department_gpa_scale'];
            $this->department_normalized_gpa = $data[0]['department_normalized_gpa'];
            $this->fgsr_gpa = $data[0]['fgsr_gpa'];
            $this->fgsr_gpa_scale = $data[0]['fgsr_gpa_scale'];
            $this->fgsr_normalized_gpa = $data[0]['fgsr_normalized_gpa'];
            $this->funding_note = $data[0]['funding_note'];
            $this->department_decision = $data[0]['department_decision'];
            $this->fgsr_decision = $data[0]['fgsr_decision'];
            $this->decision_response = $data[0]['decision_response'];
            $this->general_notes = $data[0]['general_notes'];
        }
    }

    /**
    * Returns a new GsmsData from the given id
    * @param integer $id The id of the GsmsData
    * @return GsmsData The GsmsData with the given id. If no
    * gsms exists with that id, it will return an empty gsms.
    */
    static function newFromUserId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(Cache::exists("gsms_user_$id{$dbyear}")){
            $data = Cache::fetch("gsms_user_$id{$dbyear}");
        }
        else{
            $data = DBFunctions::select(array("grand_gsms$dbyear"),
                                        array('*'),
                                        array('user_id' => EQ($id)),
                                        array('submitted_date' => 'DESC'),
                                        array(1));
            Cache::store("gsms_user_$id{$dbyear}", $data);
        }
        $gsms = new GsmsData($data, $id);
        $gsms->year = $year;
        return $gsms;
    }

    static function getAllVisibleGsms($year=""){
        global $wgRoleValues, $config;
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        $gsms_array = array();
        $me = Person::newFromWgUser();
        if($config->getValue('studyEnabled')){
            $sql = "SELECT user_id, id, max(submitted_date) as date FROM grand_gsms{$dbyear} WHERE ois_id <> '' AND ois_id IS NOT NULL GROUP BY user_id ORDER BY submitted_date";
            $data = DBFunctions::execSQL($sql);
        }
        elseif($me->isRoleAtLeast(EVALUATOR)){
            $sql = "SELECT user_id, id, max(submitted_date) as date FROM grand_gsms{$dbyear} WHERE visible = 'true' GROUP BY user_id ORDER BY submitted_date";
            $data = DBFunctions::execSQL($sql);
        }
        if(count($data) >0){
            foreach($data as $gsms){
                $gsms_array[] = GsmsData::newFromId($gsms['id'], $year);
            }
        }
        return $gsms_array;
    }
   
  /**
   * newFromId Returns an Gsms object from a given id
   * @param $id
   * @return $gsms Gsms object
   */
    static function newFromId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(Cache::exists("gsms_$id{$dbyear}")){
            $data = Cache::fetch("gsms_$id{$dbyear}");
        }
        else{
            $data = DBFunctions::select(array("grand_gsms$dbyear"),
                                        array('*'),
                                        array('id' => EQ($id)));
            Cache::store("gsms_$id{$dbyear}", $data);
        }
        $gsms = new GsmsData($data);
        $gsms->year = $year;
        return $gsms;
    }
    
    /**
   * newFromId Returns an Gsms object from a given ois id
   * @param $id
   * @return $gsms Gsms object
   */
    static function newFromOisId($id, $year=""){
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        if(Cache::exists("gsms_$id{$dbyear}")){
            $data = Cache::fetch("gsms_$id{$dbyear}");
        }
        else{
            $data = DBFunctions::select(array("grand_gsms$dbyear"),
                                        array('*'),
                                        array('ois_id' => EQ($id)));
            Cache::store("gsms_$id{$dbyear}", $data);
        }
        $gsms = new GsmsData($data);
        $gsms->year = $year;
        return $gsms;
    }

    /**
     * Returns True if the course is saved correctly to the course table in the database
     * @return boolean True if the database accepted the new course
    */
    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
                DBFunctions::insert('grand_gsms',
                                    array('`user_id`' => $this->user_id,
                                          '`student_id`' => $this->student_id,
                                          '`gender`' => $this->gender,
                                          '`gsms_id`' => $this->gsms_id,
                                          '`ois_id`' => $this->ois_id,
                                          '`applicant_number`' => $this->applicant_number,
                                          '`date_of_birth`' => $this->date_of_birth." 00:00:00",
                                          '`program_name`' => $this->program_name,
                                          '`country_of_birth`' => $this->country_of_birth,
                                          '`country_of_citizenship`' => $this->country_of_citizenship,
                                          '`applicant_type`' => $this->applicant_type,
                                          '`education_history`' => $this->education_history,
                                          '`department`' => $this->department,
                                          '`epl_test`' => $this->epl_test,
                                          '`epl_score`' => $this->epl_score,
                                          '`epl_listen`' => $this->epl_listen,
                                          '`epl_write`' => $this->epl_write,
                                          '`epl_read`' => $this->epl_read,
                                          '`epl_speaking`' => $this->epl_speaking,
                                          '`additional`' => serialize($this->additional),
                                          '`cs_app`' => $this->cs_app,
                                          '`academic_year`' => $this->academic_year,
                                          '`term`' => $this->term,
                                          '`subplan_name`' => $this->subplan_name,
                                          '`program`' => $this->program,
                                          '`degree_code`' => $this->degree_code,
                                          '`admission_program_name`' => $this->admission_program_name,
                                          '`folder`' => $this->folder,
                                          '`department_gpa`' => $this->department_gpa,
                                          '`department_gpa_scale`' => $this->department_gpa_scale,
                                          '`department_normalized_gpa`' => $this->department_normalized_gpa,
                                          '`fgsr_gpa`' => $this->fgsr_gpa,
                                          '`fgsr_gpa_scale`' => $this->fgsr_gpa_scale,
                                          '`fgsr_normalized_gpa`' => $this->fgsr_normalized_gpa,
                                          '`funding_note`' => $this->funding_note,
                                          '`department_decision`' => $this->department_decision,
                                          '`fgsr_decision`' => $this->fgsr_decision,
                                          '`decision_response`' => $this->decision_response,
                                          '`general_notes`' => $this->general_notes,
                                          '`visible`' => $this->visible));
            Cache::delete("gsms_user_{$this->user_id}");
        }
    }

    /**
     * Returns True if the course is updated correctly to the course table in the database
     * @return boolean True if the database accepted the updated course
    */
    function update(){
        $me = Person::newFromWGUser();
        /*$lastname = preg_replace('/[^\wA-zÀ-ÿ]/', '', $me->getLastName());
        $notes = (array) $this->additional["notes"];
        if ($notes[$lastname] == "") {
          unset($notes[$lastname]);
          $this->additional["notes"] = $notes;
        }*/
        if($me->isLoggedIn()){
            $status = DBFunctions::update('grand_gsms',
                                array('`gender`' => $this->gender,
                                      '`student_id`' => $this->student_id,
                                      '`gsms_id`' => $this->gsms_id,
                                      '`ois_id`' => $this->ois_id,
                                      '`applicant_number`' => $this->applicant_number,
                                      '`date_of_birth`' => $this->date_of_birth." 00:00:00",
                                      '`program_name`' => $this->program_name,
                                      '`country_of_birth`' => $this->country_of_birth,
                                      '`country_of_citizenship`' => $this->country_of_citizenship,
                                      '`applicant_type`' => $this->applicant_type,
                                      '`education_history`' => $this->education_history,
                                      '`department`' => $this->department,
                                      '`epl_test`' => $this->epl_test,
                                      '`epl_score`' => $this->epl_score,
                                      '`epl_listen`' => $this->epl_listen,
                                      '`epl_write`' => $this->epl_write,
                                      '`epl_read`' => $this->epl_read,
                                      '`epl_speaking`' => $this->epl_speaking,
                                      '`additional`' => serialize($this->additional),
                                      '`cs_app`' => $this->cs_app,
                                      '`academic_year`' => $this->academic_year,
                                      '`term`' => $this->term,
                                      '`subplan_name`' => $this->subplan_name,
                                      '`program`' => $this->program,
                                      '`degree_code`' => $this->degree_code,
                                      '`admission_program_name`' => $this->admission_program_name,
                                      '`submitted_date`' => $this->submitted_date." 00:00:00",
                                      '`folder`' => $this->folder,
                                      '`department_gpa`' => $this->department_gpa,
                                      '`department_gpa_scale`' => $this->department_gpa_scale,
                                      '`department_normalized_gpa`' => $this->department_normalized_gpa,
                                      '`fgsr_gpa`' => $this->fgsr_gpa,
                                      '`fgsr_gpa_scale`' => $this->fgsr_gpa_scale,
                                      '`fgsr_normalized_gpa`' => $this->fgsr_normalized_gpa,
                                      '`funding_note`' => $this->funding_note,
                                      '`department_decision`' => $this->department_decision,
                                      '`fgsr_decision`' => $this->fgsr_decision,
                                      '`decision_response`' => $this->decision_response,
                                      '`general_notes`' => $this->general_notes,
                                      '`visible`' => $this->visible),
			     array('user_id' => EQ($this->user_id)));
                Cache::delete("gsms_{$this->id}");
                if($this->ois_id != ""){
                    Cache::delete("gsms_{$this->ois_id}");
                }
                Cache::delete("gsms_user_{$this->user_id}");
        }
        return true;
    }

    function getProgramName($br = true){
        if($this->program_name != ""){
            // Try Program Name First
            if($br){
                return implode("<br />", explode(", ", $this->program_name));
            }
            return $this->program_name;
        }
        else {
            // Fallback to program
            $program = explode(" in", $this->program);
            $program = @$program[0];
            return $program;
        }
    }
    
    function getSOP(){
        return SOP::newFromUserId($this->user_id, $this->year);
    }

    /**
     * Returns an array of this object
     * @return array of object
    */
    function toArray(){
        global $wgUser, $config;
        $year = ($this->year != "") ? $this->year : YEAR;
        $student = Person::newFromId($this->user_id);
        $student_data = array('id' => $student->getId(),
                        'fname' => $student->getFirstName(),
                        'lname' => $student->getLastName(),
                        'name' => $student->getReversedName(),
                        'url' => $student->getUrl(),
                        'email' => $student->getEmail());
        $sop = $this->getSOP();
        $this->gsms_url = $sop->getGSMSUrl();
        if($config->getValue('networkName') == 'CSGARS'){
            $degrees = $sop->getCSEducationalHistory(true);
        }
        else{
            $degrees = $this->education_history;
        }
        $json = array('id' =>$this->id,
                  'ois_id' => $this->ois_id,
                  'user_id' =>$this->user_id,
                  'year' => $year,
                  'status' => $this->status,
                  'student_data' => $student_data,
                  'gsms_id' => $this->gsms_id,
                  'student_id' => $this->student_id,
                  'applicant_number' => $this->applicant_number,
                  'gender' => $this->gender,
                  'date_of_birth' => $this->date_of_birth,
                  'program_name' => $this->getProgramName(true),
                  'country_of_birth' => $this->country_of_birth,
                  'country_of_citizenship' => $this->country_of_citizenship,
                  'applicant_type' => $this->applicant_type,
                  'education_history' => $degrees,
                  'department' => $this->department,
                  'epl_test' => $this->epl_test,
                  'epl_score' => $this->epl_score,
                  'epl_listen' => $this->epl_listen,
                  'epl_write' => $this->epl_write,
                  'epl_read' => $this->epl_read,
                  'epl_speaking' => $this->epl_speaking,
                  'folder' => $this->folder,
                  'additional' => $this->getAdditional(),
                  'gsms_url' => $this->gsms_url,
                  'submitted_date' => $this->submitted_date);

        // Not sure if specific from here //	
        //sop information needed in table
        $json['sop_id'] = $sop->getId();
        $json['sop_url'] = $sop->getUrl();
        $json['sop_pdf'] = $sop->getSopUrl();
        $json['annotations'] = $sop->annotations;

        //adding reviewers array so can have on overview table
        $reviewers = array();
        $reviewer_array = $student->getEvaluators($year,"sop");
        foreach($reviewer_array as $reviewer){
            $person = $reviewer;
            $reviewers[] = array('id' => $person->getId(),
                                 'name' => $person->getNameForForms(),
                                 'url' => $person->getUrl(),
                                 'decision' => $sop->getAdmitResult($reviewer->getId()),
                                 'willingToSupervise' => $sop->getWillingToSupervise($reviewer->getId()),
                                 'comments' => $sop->getReviewComments($reviewer->getId()),
                                 'rank' => $sop->getReviewRanking($reviewer->getId()),
                                 'hidden' => $sop->getHiddenStatus($reviewer->getId()));
        }
        $json['reviewers'] = $reviewers;


        $otherReviewers = array();
        
        $other_array = $student->getOtherEvaluators($year);
        foreach($other_array as $other){
            $otherReviewers[] = array('id' => $other->getId(),
                                      'name' => $other->getNameForForms(),
                                      'url' => $other->getUrl(),
                                      'decision' => $sop->getAdmitResult($other->getId()),
                                      'willingToSupervise' => $sop->getWillingToSupervise($other->getId()),
                                      'comments' => $sop->getReviewComments($other->getId()),
                                      'rank' => $sop->getReviewRanking($other->getId()),
                                      'hidden' => $sop->getHiddenStatus($other->getId()));
        }
        
        $json['other_reviewers'] = $otherReviewers;
        

        //adding decisions by boards
        $json['admit'] = $sop->getFinalAdmit();
        $supervisors = $this->getAssignedSupervisors();
        $json['supervisor'] = @implode(", ", $supervisors['q5']);
        $json['comments'] = $sop->getFinalComments();
        $json['area'] = "";
        $json['degree'] = $this->getFinalProgram();
        $json['ftpt'] = $this->getFullTimePartTime();

        if($config->getValue('networkName') == 'GARS'){

           //adding nationality as one string
            $nationality = array();
            $additionals = $this->getAdditional();
            $nationality[] = ($additionals['indigenous'] == "Yes") ? "Indigenous" : "";
            $nationality[] = ($additionals['canadian'] == "Yes") ? "Canadian" : "";
            $nationality[] = ($additionals['saskatchewan'] == "Yes") ? "Saskatchewan" : "";
            $nationality[] = ($additionals['international'] == "Yes") ? "International" : "";

            $nationality_note = "";
            foreach($nationality as $note){
                if($note != ""){
                    $nationality_note .= $note.',<br />';
                }    
            }
            $json['nationality_note'] = $nationality_note;
        }
        if($config->getValue('networkName') == 'CSGARS'){
            $json['additional'] = array_merge($json['additional'],$sop->getColumns());
        }

        // Needed by exportAdmittedStudents
        $json['academic_year'] = $this->academic_year;
        $json['term'] = $this->term;

        return $json;

    }

    function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0, $subItem=0){
        if ($userId === null) {
          $userId = $this->user_id;
        }
        $blb = new ReportBlob($blobType, $year, $userId, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, $subItem);
        $result = $blb->load($addr);
        $data = $blb->getData();

        return $data;
    }

    function getOTColumns(){
        $gsms_array = array('gpa60' => "",
                            'gpafull' => "",
                            'gpafull_credits' => "",
                            'gpafull2' => "",
                            'gpafull_credits2' => "",
                            'notes' => "",
                            'casper' => "",
                            'indigenous' => "",
                            'canadian' => "",
                            'saskatchewan' => "",
                            'international' =>"",
                            'withdrawals' => "",
                            'anatomy' => "",
                            'stats' => "",
                            'failures' => "",
                            'degrees' => array());
        return $gsms_array;
    }

    function getCSColumns() {
        $year = ($this->year != "") ? $year : YEAR;
        $moreJson = array();
        $AoS = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q13");
        $moreJson['areas_of_study'] = @implode(", ", $AoS['q13']);
        //var_dump($moreJson['areas_of_study']);

        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q14");
        #$moreJson['supervisors'] = @implode(";\n", explode(" ", $blob['q14'])[1]);
        $supervisors = "";
        if (isset($blob['q14'])) {
          foreach ($blob['q14'] as $el) {
            $sup_array = explode(" ", $el);
            $supervisors[] = array("first" => @$sup_array[0], 
                                   "last"  => @$sup_array[1]);
          }
        }
        $moreJson['supervisors'] = $supervisors;

        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q16");
        $moreJson['scholarships_held'] = @implode(", ", $blob['q16']);

        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q15");
        $moreJson['scholarships_applied'] = @implode(", ", $blob['q15']);

        $moreJson['gpaNormalized'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q21");
        $moreJson['gre1'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q24");
        $moreJson['gre2'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q25");
        $moreJson['gre3'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q26");
        $moreJson['gre4'] = $this->getBlobValue(BLOB_TEXT, $year, "RP_CS", "CS_QUESTIONS_tab1", "Q27");

        // # of Publications
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab3", "qPublications");
        $moreJson['num_publications'] = @count($blob['qResExp2']);

        // # of awards
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab4", "qAwards");
        $moreJson['num_awards'] = @count($blob['qAwards']);

        // Courses (number of courses, number of areas)
        $blob = $this->getBlobValue(BLOB_ARRAY, $year, "RP_CS", "CS_QUESTIONS_tab6", "qCourses");
        $courses = array();
        //var_dump($blob);
        //exit;
        if (isset($blob['qEducation2'])) {
          foreach ($blob['qEducation2'] as $el) {
            $courses[] = $el['course'];
          }
        }
        $moreJson['courses'] = @implode(", ", $courses);
        //$moreJson['courses'] = @implode(", ", $blob['qEducation2'][0]);

        return $moreJson;

    }

    function getAdditional(){
        if($this->additional == "" || !is_array($this->additional) || count($this->additional) == 0){
            return $this->getOTColumns(); //!!!! THIS NEEDS TO CHANGE ONCE CS HAS THEIR OWN STUFF. ALSO, NEED TO FIGURE OUT HOW TO DEAL WITH INDEXES THAT DON'T EXIST IN ADDITIONALS !!!! /////
        }
        return $this->additional;

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

    function getFinalProgram() {
        $prog = $this->admission_program_name;
        if (strpos($prog, "Master of Science (Thes)") !== false) {
            return "MSc";
        } else if (strpos($prog, "Master of Science (Crse)") !== false) {
            return "MSc-C";
        } else if (strpos($prog, "Doctor of Philosophy") !== false) {
            return "PhD";
        }
        return $prog;
    }

    function getFullTimePartTime() {
        $prog = $this->program;
        $progSplit = explode(" - ", $prog);
        $time = isset($progSplit[1]) ? $progSplit[1] : "";
        if (strtolower($time) == "full time") {
          $time = "FT";
        } else if (strtolower($time) == "part time") {
          $time = "PT";
        }
        return $time;
    }

    function getAssignedSupervisors() {
        $year = ($this->year != "") ? $this->year : YEAR;
        return $this->getBlobValue(BLOB_ARRAY, $year, "RP_COM", "OT_COM", "Q14", 0, $this->getId());
    }

    function getFunding() {
        $year = ($this->year != "") ? $this->year : YEAR;
        return $this->getBlobValue(BLOB_TEXT, $year, "RP_COM", "OT_COM", "Q4", 0, $this->getSOP()->id, $this->getSOP()->id);
    }
}

?>
