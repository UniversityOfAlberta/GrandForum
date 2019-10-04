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
    var $visible;
    var $additional = array(); 
    var $gsms_url;

//added just incase
    var $submitted_date;

    function GsmsData($data){
        global $config;
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->user_id = $data[0]['user_id'];
            $this->visible = $data[0]['visible'];
            $this->student_id = $data[0]['student_id'];
            $this->gsms_id = $data[0]['gsms_id'];
            $this->ois_id = $data[0]['ois_id'];
            $this->additional = json_decode($data[0]['additional'], true);
            $sub = explode(" ",$data[0]['submitted_date']);
            $this->submitted_date = $sub[0];
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
        global $wgRoleValues;
        $dbyear = ($year != "" && $year != YEAR) ? "_$year" : "";
        $gsms_array = array();
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(EVALUATOR)){
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
                                          '`gsms_id`' => $this->gsms_id,
                                          '`ois_id`' => $this->ois_id,
                                          '`additional`' => json_encode($this->additional),
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
                                array('`student_id`' => $this->student_id,
                                      '`gsms_id`' => $this->gsms_id,
                                      '`ois_id`' => $this->ois_id,
                                      '`additional`' => json_encode($this->additional),
                                      '`submitted_date`' => $this->submitted_date." 00:00:00",
                                      '`visible`' => $this->visible),
			     array('user_id' => EQ($this->user_id)));
                Cache::delete("gsms_", true);
                Cache::delete("gsms_user_", true);
        }
        return true;
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
            $this->setAdditional("education_history", $sop->getCSEducationalHistory(true));
        }
        $json = array('id' =>$this->id,
                  'ois_id' => $this->ois_id,
                  'user_id' =>$this->user_id,
                  'year' => $year,
                  'student_data' => $student_data,
                  'gsms_id' => $this->gsms_id,
                  'student_id' => $this->student_id,
                  'additional' => $this->getAdditional(),
                  'gsms_url' => $this->gsms_url);

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
                                      'rank' => $sop->getReviewRanking($other->getId()),
                                      'hidden' => $sop->getHiddenStatus($other->getId()));
        }
        
        $json['other_reviewers'] = $otherReviewers;
        

        //adding decisions by boards
        $json['admit'] = $sop->getFinalAdmit();
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

    /*function getOTColumns(){
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
    }*/

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
            $supervisors[] = $sup_array[1];
          }
        }
        $moreJson['supervisors'] = @nl2br(implode(",\n", $supervisors));

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

    function getAdditional($field="", $default=""){
        if($field == ""){
            return $this->additional;
        }
        else{
            return (isset($this->additional[$field]) && $this->additional[$field] != "") ? $this->additional[$field] : $default;
        }
    }
    
    function setAdditional($field, $value){
        if(!is_array($this->additional)){
            $this->additional = array();
        }
        $this->additional[$field] = $value;
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
        $prog = $this->getAdditional('admission_program_name');
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
        $prog = $this->getAdditional("program");
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
        $year = ($this->year != "") ? $year : YEAR;
        return $this->getBlobValue(BLOB_ARRAY, $year, "RP_COM", "OT_COM", "Q14", 0, $this->getSOP()->id);
    }

    function getFunding() {
        $year = ($this->year != "") ? $year : YEAR;
        return $this->getBlobValue(BLOB_TEXT, $year, "RP_COM", "OT_COM", "Q4", 0, $this->getSOP()->id, $this->getSOP()->id);
    }
}

?>
