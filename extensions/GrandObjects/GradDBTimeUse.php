<?php
    /**
    * @package GrandObjects
    */

class GradDBTimeUse extends BackboneModel{
    
    var $id;
    var $userId;
    var $term;
    var $md5;
    var $hours;
    var $type;
    var $start;
    var $end;
    var $gta = array();
    var $gra = array();
    var $graf = array();
    var $vacation = array();
    var $hqpAccepted;
    var $supervisorsAccepted = array();
    var $pdf;
    var $html;

    static function newFromId($id){
        $data = DBFunctions::select(array('grand_graddb_timeuse'),
                                    array('id',
                                          'user_id',
                                          'term',
                                          'md5',
                                          'hours',
                                          'start',
                                          'end',
                                          'gta',
                                          'gra',
                                          'graf',
                                          'vacation',
                                          'hqpAccepted',
                                          'supervisorsAccepted'),
                                    array('id' => EQ($id)));
        return new GradDBTimeUse($data);
    }
    
    static function newFromMd5($md5){
        $data = DBFunctions::select(array('grand_graddb_timeuse'),
                                    array('id',
                                          'user_id',
                                          'term',
                                          'md5',
                                          'hours',
                                          'start',
                                          'end',
                                          'gta',
                                          'gra',
                                          'graf',
                                          'vacation',
                                          'hqpAccepted',
                                          'supervisorsAccepted'),
                                    array('md5' => EQ($md5)));
        return new GradDBTimeUse($data);
    }
    
    static function newFromTuple($user_id, $term){
        $data = DBFunctions::select(array('grand_graddb_timeuse'),
                                    array('id',
                                          'user_id',
                                          'term',
                                          'md5',
                                          'hours',
                                          'start',
                                          'end',
                                          'gta',
                                          'gra',
                                          'graf',
                                          'vacation',
                                          'hqpAccepted',
                                          'supervisorsAccepted'),
                                    array('user_id' => EQ($user_id),
                                          'term' => EQ($term)));
        $obj = new GradDBTimeUse($data);
        $obj->userId = $user_id;
        $obj->supervisor = $supervisor;
        $obj->term = $term;
        return $obj;
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

    // Constructor
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->userId = $data[0]['user_id'];
            $this->term = $data[0]['term'];
            $this->md5 = $data[0]['md5'];
            $this->hours = $data[0]['hours'];
            $this->start = $data[0]['start'];
            $this->end = $data[0]['end'];
            $this->gta = json_decode($data[0]['gta']);
            $this->gra = json_decode($data[0]['gra']);
            $this->graf = json_decode($data[0]['graf']);
            $this->vacation = json_decode($data[0]['vacation']);
            $this->hqpAccepted = $data[0]['hqpAccepted'];
            $this->supervisorsAccepted = json_decode($data[0]['supervisorsAccepted']);
        }
    }
    
    // Check if user is allowed to view the data of this GradDBTimeUse
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            return true;
        }
        if($this->getHQP()->getId() == $me->getId() ||
           $this->getSupervisor()->getId() == $me->getId()){
            return true;  
        }
        return false;
    }

    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            DBFunctions::insert('grand_graddb_timeuse',
                                array('user_id',
                                      'term',
                                      'md5',
                                      'hours',
                                      'start',
                                      'end',
                                      'gta',
                                      'gra',
                                      'graf',
                                      'vacation',
                                      'hqpAccepted',
                                      'supervisorsAccepted'));
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
        }
    }

    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            DBFunctions::update('grand_graddb_timeuse',
                                array('user_id' => $this->userId,
                                      'supervisor' => $this->supervisor,
                                      'term' => $this->term,
                                      'md5' => $this->getMD5(),
                                      'account' => $this->account,
                                      'type' => $this->type,
                                      'hours' => $this->hours,
                                      'percent' => $this->percent,
                                      'start' => $this->start,
                                      'end' => $this->end,
                                      'supervisorAccepted' => $this->supervisorAccepted,
                                      'hqpAccepted' => $this->hqpAccepted),
                                array('id' => EQ($this->id)));
            DBFunctions::commit();
        }
    }

    function toArray(){
        //TODO:implement function
    }
    
    function delete(){
        if($me->isLoggedIn()){
            DBFunctions::delete('grand_graddb_timeuse',
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
    
    function getSupervisor(){
        return Person::newFromId($this->supervisor);
    }

    function getMD5(){
        if($this->md5 == ""){
            $rand = rand(0, 10000);
            $this->md5 = md5("{$rand}_{$this->userId}_{$this->supervisor}_{$this->getTerm()}");
        }
        return $this->md5;
    }
    
    function getTerm(){
        return $this->term;
    }
    
    function getHours(){
        return $this->hours;
    }
    
    function getPercent(){
        return $this->percent;
    }
    
    function getStart(){
        if($this->start == ""){
            return self::term2Date($this->term);
        }
        return substr($this->start, 0, 10);
    }
    
    function getEnd(){
        if($this->end == ""){
            return self::term2Date($this->term, true);
        }
        return substr($this->end, 0, 10);
    }
    
    function getHQPAccepted(){
        return $this->hqpAccepted;
    }
    
    function getSupervisorAccepted(){
        return $this->supervisorAccepted;
    }
    
    function hasHQPAccepted(){
        return ($this->hqpAccepted != "0000-00-00 00:00:00");
    }
    
    function hasSupervisorAccepted(){
        return ($this->supervisorAccepted != "0000-00-00 00:00:00");
    }
    
    function getPDF(){
        $data = DBFunctions::select(array('grand_graddb_timeuse'),
                                    array('pdf'),
                                    array('id' => EQ($this->id)));
        return $data[0]['pdf'];
    }
    
    function generatePDF(){
        global $wgServer, $wgScriptPath;
        $date = date('d F Y');
        $start = date('d F Y', strtotime($this->getStart()));
        $end = date('d F Y', strtotime($this->getEnd()));
        
        $supervisorAccepted = "";
        $hqpAccepted = "";
        if($this->hasSupervisorAccepted()){
            $supervisorAccepted = "<p>Prepared by {$this->getSupervisor()->getFullName()}: <b>{$this->getSupervisorAccepted()}</b></p>";
        }
        if($this->hasHQPAccepted()){
            $hqpAccepted = "<p>Accepted by {$this->getHQP()->getFullName()}: <b>{$this->getHQPAccepted()}</b></p>";
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
                    <li>Type of Appointment: <b>{$this->getType()}</b></li>
                    <li>Period of Appointment: <b>{$start}</b> to <b>{$end}</b></li>
                    <li>Maximum Hours Assigned Per Week: <b>{$this->getHours()}</b></li>
                    <li>Stipend Per Term: Award: <b>$1800</b> Salary: <b>$2519</b> Total Stipend: <b>$4319</b></li>
                    <li>Graduate Assistantship Supervisor: <b>{$this->getSupervisor()->getFullName()}</b></li>
                    <li>At the beginning of the term, the Graduate Assistantship Supervisor will meet with you to complete the Assistantship Time Use Guidelines Form (refer to Appendix C of the Graduate Student Assistantship Collective Agreement), which will form part of the graduate assistantship appointment. Note: the nature of your duties may vary from term to term depending on the needs of the department, available graduate assistantships and external factors.</li>
                    <li>The graduate assistantship offer is subject to the maintenance of satisfactory academic standing in the graduate program, as defined in the Faculty of Graduate Studies & Research Graduate Policy Manual, the Department's Graduate Studies Manual, and on satisfactory completion of the assigned duties of the graduate assistantship.</li>
                    <li>If you are not a Canadian citizen, this appointment is expressly contingent upon you meeting and continuing to meet eligibility requirements for employment, as set out in the Immigration and Refugee Protection Act and Regulations. It is further contingent upon the University of Alberta receiving regular \"confirmation,\" if required by Service Canada. Should you be ineligible for employment at any time, or should the University of Alberta be unable to obtain \"confirmation\" if required, this appointment shall be rendered null and void effective immediately.</li>
                    <li>Failure to report to the department by the appointment start date indicated above may result in termination of this offer of appointment without further notification to you.</li>
                    <li>The criteria for selecting graduate students for assistantships is outlined in the Department of Computing Science Financial Support Policy.</li>
                    <li>This assistantship may not cover the full cost of living in Edmonton and your graduate tuition and fees. To prevent disputes, misunderstandings and continuous progress in program, it is suggested that the undersigned maintain a personal record of hours worked and duties performed as a GRA/GTA/GTA-PI.</li>
                </ul>
                <br />
                {$supervisorAccepted}
                {$hqpAccepted}
                </div>";
        $pdf = PDFGenerator::generate("Funding", $html, "", null, null, false, null, false);
        $this->pdf = $pdf['pdf'];
        $this->html = $pdf['html'];
        DBFunctions::update('grand_graddb_timeuse',
                            array('supervisorAccepted' => $this->supervisorAccepted,
                                  'hqpAccepted' => $this->hqpAccepted,
                                  'pdf' => $this->pdf),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
    }

}

?>
