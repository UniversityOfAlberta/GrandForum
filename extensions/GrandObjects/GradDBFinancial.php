<?php
    /**
    * @package GrandObjects
    */

class GradDBFinancial extends BackboneModel{
    
    var $id;
    var $userId;
    var $term;
    var $md5;
    var $hqpAccepted = 0;
    var $supervisors = array();
    var $pdf;
    var $html;

    static function newFromId($id){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id',
                                          'user_id',
                                          'term',
                                          'md5',
                                          'hqpAccepted',
                                          'supervisors'),
                                    array('id' => EQ($id)));
        return new GradDBFinancial($data);
    }
    
    static function newFromMd5($md5){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id',
                                          'user_id',
                                          'term',
                                          'md5',
                                          'hqpAccepted',
                                          'supervisors'),
                                    array('md5' => EQ($md5)));
        return new GradDBFinancial($data);
    }
    
    static function getAllFromHQP($user_id){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id'),
                                    array('user_id' => EQ($user_id)));
        $objs = array();
        foreach($data as $row){
            $objs[] = new GradDBFinancial(array($row));
        }
        return $objs;
    }
    
    static function newFromTuple($user_id, $term){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id',
                                          'user_id',
                                          'term',
                                          'md5',
                                          'hqpAccepted',
                                          'supervisors'),
                                    array('user_id' => EQ($user_id),
                                          'term' => LIKE("%$term%")));
        $obj = new GradDBFinancial($data);
        $obj->userId = $user_id;
        $obj->term = $term;
        return $obj;
    }
    
    static function yearTerms($term){
        $year = substr($term, -4);
        if(strstr($term, "Winter") !== false){
            return array("Winter{$year}",
                         "Spring/Summer{$year}",
                         "Fall{$year}");
        }
        if(strstr($term, "Spring/Summer") !== false){
            return array("Spring/Summer{$year}",
                         "Fall{$year}",
                         "Winter".($year+1));
        }
        if(strstr($term, "Fall") !== false){
            return array("Fall{$year}",
                         "Winter".($year+1),
                         "Spring/Summer".($year+1));
        }
    }
    
    static function term2Date($term, $end=false){
        $month = "01-01";
        $year = substr($term, -4);
        if(!$end){
            // Start Dates
            if(strstr($term, "Winter") !== false){
                $month = "-01-01";
            }
            else if(strstr($term, "Spring/Summer") !== false){
                $month = "-05-01";
            }
            else if(strstr($term, "Fall") !== false){
                $month = "-09-01";
            }
        }
        else{
            // End Dates
            if(strstr($term, "Winter") !== false){
                $month = "-04-30";
            }
            else if(strstr($term, "Spring/Summer") !== false){
                $month = "-08-31";
            }
            else if(strstr($term, "Fall") !== false){
                $month = "-12-31";
            }
        }
        return "{$year}{$month}";
    }
    
    // Returns HQPs who have been attached to the 
    function getAttachedHQP($supId, $term){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('user_id'),
                                    array('supervisors' => LIKE("%\"supervisor\":\"{$supId}\"%"),
                                          'term' => LIKE("%{$term}%")));
        $hqps = array();
        foreach($data as $row){
            $hqp = Person::newFromId($row['user_id']);
            if($hqp != null && $hqp->getId() != 0){
                $hqps[$hqp->getName()] = $hqp;
            }
        }
        return $hqps;
    }

    // Constructor
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->userId = $data[0]['user_id'];
            $this->term = $data[0]['term'];
            $this->md5 = $data[0]['md5'];
            $this->hqpAccepted = $data[0]['hqpAccepted'];
            $this->supervisors = json_decode($data[0]['supervisors'], true);
        }
        if(count($this->supervisors) == 0){
            $this->supervisors[] = $this->emptySupervisor();
        }
    }
    
    // Check if user is allowed to view the data of this GradDBFinancial
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            return true;
        }
        if($this->getHQP()->getId() == $me->getId()){
            return true;  
        }
        if($this->isSupervisor($me->getId())){
            return true;
        }
        return false;
    }

    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            DBFunctions::insert('grand_graddb',
                                array('user_id' => $this->userId,
                                      'term' => $this->term,
                                      'md5' => $this->getMD5(),
                                      'supervisors' => json_encode($this->supervisors),
                                      'hqpAccepted' => $this->hqpAccepted));
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
        }
    }

    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            DBFunctions::update('grand_graddb',
                                array('user_id' => $this->userId,
                                      'term' => $this->term,
                                      'md5' => $this->getMD5(),
                                      'supervisors' => json_encode($this->supervisors),
                                      'hqpAccepted' => $this->hqpAccepted),
                                array('id' => EQ($this->id)));
            DBFunctions::commit();
        }
    }
    
    function emptySupervisor($supId=0, $type="GTA", $account="", $hours=12, $percent=100, $acceptedDate=null){
        return array("supervisor" => $supId,
                     "type" => $type,
                     "account" => $account,
                     "hours" => $hours,
                     "percent" => $percent,
                     "accepted" => $acceptedDate);
    }

    function toArray(){
        //TODO:implement function
    }
    
    function delete(){
        if($me->isLoggedIn()){
            DBFunctions::delete('grand_graddb',
                                array('id' => EQ($this->id)));
        }
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        //TODO:implement function
    }

    function getId(){
        return $this->id;
    }
    
    function getHQP(){
        return Person::newFromId($this->userId);
    }
    
    function getSupervisors($init=false){
        if(!$init){
            return $this->supervisors;
        }
        else{
            $supervisors = array();
            foreach($this->supervisors as $key => $sup){
                $supervisors[] = $sup;
                $supervisors[$key]['supervisor'] = Person::newFromId($supervisors[$key]['supervisor']);
            }
            return $supervisors;
        }
    }
    
    function setSupervisorField($supId, $key, $value){
        foreach($this->getSupervisors() as $k => $sup){
            if($sup['supervisor'] == $supId){
                $this->supervisors[$k][$key] = $value;
                break;
            }
        }
    }

    function getMD5(){
        if($this->md5 == ""){
            $rand = rand(0, 10000);
            $this->md5 = md5("{$rand}_{$this->userId}_{$this->getTerm()}");
        }
        return $this->md5;
    }
    
    function getTerm(){
        return $this->term;
    }
    
    function getTerms(){
        return explode(",", $this->term);
    }
    
    function getAward(){
        $awardFor12Hours = 900;
        $nMonths = count($this->getTerms())*4;
        $hours = 0;
        foreach($this->getSupervisors() as $sup){
            if($sup['hours'] != "N/A"){
                $hours = max($hours, $sup['hours']);
            }
        }
        return $awardFor12Hours*($hours/12)*$nMonths;
    }
    
    function getStart(){
        $terms = $this->getTerms();
        return self::term2Date($terms[0]);
    }
    
    function getEnd(){
        $terms = $this->getTerms();
        return self::term2Date($terms[count($terms)-1], true);
    }
    
    function getHQPAccepted(){
        return $this->hqpAccepted;
    }
    
    function hasHQPAccepted(){
        return ($this->hqpAccepted != "0000-00-00 00:00:00");
    }
    
    function isSupervisor($supId){
        foreach($this->getSupervisors() as $sup){
            if($sup['supervisor'] == $supId){
                return true;
            }   
        }
        return false;
    }
    
    function hasSupervisorAccepted($supId){
        foreach($this->getSupervisors() as $sup){
            if($sup['supervisor'] == $supId){
                return ($sup['accepted'] != null);
            }
        }
        return false;
    }
    
    function getPDF(){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('pdf'),
                                    array('id' => EQ($this->id)));
        return $data[0]['pdf'];
    }
    
    function generatePDF(){
        global $wgServer, $wgScriptPath;
        $date = date('d F Y');
        $start = date('d F Y', strtotime($this->getStart()));
        $end = date('d F Y', strtotime($this->getEnd()));
        
        $names = array();
        $types = array();
        $accepted = array();
        $hours = 0;
        foreach($this->getSupervisors(true) as $sup){
            $supervisor = $sup['supervisor'];
            $names[] = "{$supervisor->getFullName()} ({$sup['percent']}%)";
            $types[$sup['type']] = $sup['type'];
            if($sup['hours'] != "N/A"){
                $hours = max($hours, $sup['hours']);
            }
            if($sup['accepted'] != null){
                $accepted[$supervisor->getId()] = "<p>Accepted by {$supervisor->getFullName()}: <b>{$sup['accepted']}</b></p>";
            }
            else{
                $accepted[$supervisor->getId()] = "<p>Not yet accepted by {$supervisor->getFullName()}</p>";
            }
        }
        if($hours == 0){
            $hours = "N/A";
        }

        if($this->hasHQPAccepted()){
            $accepted[$this->getHQP()->getId()] = "<p>Accepted by {$this->getHQP()->getFullName()}: <b>{$this->getHQPAccepted()}</b></p>";
        }
        else {
            $accepted[$this->getHQP()->getId()] = "<p>Not yet accepted by {$this->getHQP()->getFullName()}</p>";
        }
        
        $html = "<style>
                    li {
                        padding-bottom: 0.25em;
                    }
                 </style>
                 <div style='font-size: 1.25em; padding: 1.25em;'>
                 <div style='float:right; text-align: right;'>
                    <span style='font-size:1.5em;'>Department of Computing Science</span><br />
                    <span style='font-size:0.9em;'>2-21 Athabasca Hall | University of Alberta | Edmonton, Alberta T6G 2E8</span>
                 </div>
                 <img src='skins/UAlberta_Black.png' style='height: 3em;' />
                 <hr />
                 <br />
                 <p>{$date}</p><br />
                 <p>{$this->getHQP()->getFullName()}<br />
                    {$this->getHQP()->getEmail()}</p>
                <br />
                <p>Dear {$this->getHQP()->getFullName()},<br />
                <br />
                We are pleased to offer you an appointment as a graduate assistant at the University of Alberta in accordance with the terms set out below. Should you accept this offer, your appointment will be governed by the Collective Agreement Governing Graduate Assistantships. The Agreement may be amended in accordance with terms of the Collective Agreement and such amendments are binding upon the University and the graduate assistant.</p>
                <br />
                <ul type='a' style='margin-left: 1em;' >
                    <li>Type of Appointment: <b>".implode(", ", $types)."</b></li>
                    <li>Period of Appointment: <b>{$start}</b> to <b>{$end}</b></li>
                    <li>Maximum Hours Assigned Per Week: <b>{$hours}</b></li>
                    <li>Stipend Per Term: Award: <b>\${$this->getAward()}</b> Salary: <b>\$YYYY</b> Total Stipend: <b>\$ZZZZ</b></li>
                    <li>Graduate Assistantship Supervisor(s): <b>".implode(", ", $names)."</b></li>
                    <li>At the beginning of the term, the Graduate Assistantship Supervisor will meet with you to complete the Assistantship Time Use Guidelines Form (refer to Appendix C of the Graduate Student Assistantship Collective Agreement), which will form part of the graduate assistantship appointment. Note: the nature of your duties may vary from term to term depending on the needs of the department, available graduate assistantships and external factors.</li>
                    <li>The graduate assistantship offer is subject to the maintenance of satisfactory academic standing in the graduate program, as defined in the Faculty of Graduate Studies & Research Graduate Policy Manual, the Department's Graduate Studies Manual, and on satisfactory completion of the assigned duties of the graduate assistantship.</li>
                    <li>If you are not a Canadian citizen, this appointment is expressly contingent upon you meeting and continuing to meet eligibility requirements for employment, as set out in the Immigration and Refugee Protection Act and Regulations. It is further contingent upon the University of Alberta receiving regular \"confirmation,\" if required by Service Canada. Should you be ineligible for employment at any time, or should the University of Alberta be unable to obtain \"confirmation\" if required, this appointment shall be rendered null and void effective immediately.</li>
                    <li>Failure to report to the department by the appointment start date indicated above may result in termination of this offer of appointment without further notification to you.</li>
                    <li>The criteria for selecting graduate students for assistantships is outlined in the Department of Computing Science Financial Support Policy.</li>
                    <li>This assistantship may not cover the full cost of living in Edmonton and your graduate tuition and fees. To prevent disputes, misunderstandings and continuous progress in program, it is suggested that the undersigned maintain a personal record of hours worked and duties performed as a GRA/GTA/GTA-PI.</li>
                </ul>
                <br />
                ".implode("\n", $accepted)."
                </div>";
        $pdf = PDFGenerator::generate("Funding", $html, "", null, null, false, null, false);
        $this->pdf = $pdf['pdf'];
        $this->html = $pdf['html'];
        DBFunctions::update('grand_graddb',
                            array('pdf' => $this->pdf),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
    }

}

?>
