<?php
    /**
    * @package GrandObjects
    */

class GradDBFinancial extends BackboneModel{
    
    var $id;
    var $hqpId;
    var $supId;
    var $term;
    var $md5;
    var $position;
    var $hqpAccepted = 0;
    var $supAccepted = 0;
    var $lines = array();
    var $pdf;
    var $terminated;
    var $html;
    
    static $GRAF_STIPEND = 8891;
    static $HOURS = 12;
    
    static function getScale($person, $year){
        // TODO: May need to switch to a specific db table to store a student's program status, similar to the grand_personal_fec_info
        $university = $person->getUniversity();
        $start = new DateTime("$year-09-01");
        $end = new DateTime($university['start']);
        $interval = $end->diff($start);
        $days = $interval->format('%R%a days');
        $years = $days/365;
        $step = 1;
        if(in_array(strtolower($university['position']), Person::$studentPositions['msc'])){
            $step = min(1 + floor($years), 6);
        }
        else if(in_array(strtolower($university['position']), Person::$studentPositions['phd'])){
            $extra = 0;
            if(ceil($years) > 5){ $extra++; }
            if(ceil($years) > 8){ $extra++; }
            $step = min(3 + floor($years) + $extra, 13);
        }
        $data = DBFunctions::select(array('grand_graddb_salary_scales'),
                                    array('award', "step{$step}"),
                                    array('year' => EQ($year)));
        if(count($data)){
            return array('award' => $data[0]['award'],
                         'salary' => $data[0]["step{$step}"]);
        }
        else{
            return array('award' => 0,
                         'salary' => 0);
        }
    }

    static function newFromId($id){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id',
                                          'hqp',
                                          'supervsor',
                                          'term',
                                          'md5',
                                          'position',
                                          'hqpAccepted',
                                          'supAccepted',
                                          '`lines`',
                                          '`terminated`'),
                                    array('id' => EQ($id)));
        return new GradDBFinancial($data);
    }
    
    static function newFromMd5($md5){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id',
                                          'hqp',
                                          'supervisor',
                                          'term',
                                          'md5',
                                          'position',
                                          'hqpAccepted',
                                          'supAccepted',
                                          '`lines`',
                                          '`terminated`'),
                                    array('md5' => EQ($md5),
                                          '`terminated`' => NEQ(1)));
        return new GradDBFinancial($data);
    }
    
    static function getAll(){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('*'),
                                    array('`terminated`' => NEQ(1)));
        $objs = array();
        foreach($data as $row){
            $objs[] = new GradDBFinancial(array($row));
        }
        return $objs;
    }
    
    static function getAllByTerm($term){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('*'),
                                    array('term' => LIKE("%$term%"),
                                          '`terminated`' => NEQ(1)));
        $objs = array();
        foreach($data as $row){
            $objs[] = new GradDBFinancial(array($row));
        }
        return $objs;
    }
    
    static function getAllFromHQP($hqp_id){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('*'),
                                    array('hqp' => EQ($hqp_id),
                                          '`terminated`' => NEQ(1)));
        $objs = array();
        foreach($data as $row){
            $objs[] = new GradDBFinancial(array($row));
        }
        return $objs;
    }
    
    static function newFromTuple($hqp_id, $sup_id, $term){
        $data = DBFunctions::select(array('grand_graddb'),
                                    array('id',
                                          'hqp',
                                          'supervisor',
                                          'term',
                                          'md5',
                                          'position',
                                          'hqpAccepted',
                                          'supAccepted',
                                          '`lines`',
                                          '`terminated`'),
                                    array('hqp' => EQ($hqp_id),
                                          'supervisor' => EQ($sup_id),
                                          'term' => LIKE("%$term%"),
                                          '`terminated`' => NEQ(1)));
        $obj = new GradDBFinancial($data);
        $obj->hqpId = $hqp_id;
        $obj->term = $term;
        return $obj;
    }
    
    static function prevTerm($term){
        $year = substr($term, -4);
        if(strstr($term, "Winter") !== false){
            return "Fall".($year-1);
        }
        if(strstr($term, "Spring/Summer") !== false){
            return "Winter".($year);
        }
        if(strstr($term, "Fall") !== false){
            return "Spring/Summer".($year);
        }
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
                                    array('hqp'),
                                    array('supervisor' => EQ($supId),
                                          'term' => LIKE("%{$term}%")));
        $hqps = array();
        foreach($data as $row){
            $hqp = Person::newFromId($row['hqp']);
            if($hqp != null && $hqp->getId() != 0){
                $hqps[strtolower($hqp->getName())] = $hqp;
            }
        }
        return $hqps;
    }

    // Constructor
    function GradDBFinancial($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->hqpId = $data[0]['hqp'];
            $this->supId = $data[0]['supervisor'];
            $this->term = $data[0]['term'];
            $this->md5 = $data[0]['md5'];
            $this->position = $data[0]['position'];
            $this->hqpAccepted = $data[0]['hqpAccepted'];
            $this->supAccepted = $data[0]['supAccepted'];
            $this->lines = json_decode($data[0]['lines'], true);
            $this->terminated = $data[0]['terminated'];
        }
        if(count($this->lines) == 0){
            $this->lines[] = $this->emptyLine();
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
            $university = $this->getHQP()->getUniversity();
            $this->position = $university['position'];
            DBFunctions::insert('grand_graddb',
                                array('hqp' => $this->hqpId,
                                      'supervisor' => $this->supId,
                                      'term' => $this->term,
                                      'md5' => $this->getMD5(),
                                      'position' => $this->position,
                                      '`lines`' => json_encode($this->lines),
                                      'hqpAccepted' => $this->hqpAccepted,
                                      'supAccepted' => $this->supAccepted,
                                      '`terminated`' => $this->terminated));
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
        }
    }

    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            DBFunctions::update('grand_graddb',
                                array('hqp' => $this->hqpId,
                                      'supervisor' => $this->supId,
                                      'term' => $this->term,
                                      'md5' => $this->getMD5(),
                                      'position' => $this->position,
                                      '`lines`' => json_encode($this->lines),
                                      'hqpAccepted' => $this->hqpAccepted,
                                      'supAccepted' => $this->supAccepted,
                                      '`terminated`' => $this->terminated),
                                array('id' => EQ($this->id)));
            DBFunctions::commit();
        }
    }
    
    function emptyLine($type="GTA", $account="", $hours=12, $award=0, $salary=0, $stipend=0){
        return array("type" => $type,
                     "account" => $account,
                     "hours" => $hours,
                     "award" => $award,
                     "salary" => $salary,
                     "stipend" => $stipend);
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
        return Person::newFromId($this->hqpId);
    }
    
    function getSupervisor(){
        return Person::newFromId($this->supId);
    }
    
    function getLines(){
        return $this->lines;
    }

    function getMD5(){
        if($this->md5 == ""){
            $rand = rand(0, 10000);
            $this->md5 = md5("{$rand}_{$this->hqpId}_{$this->supId}_{$this->getTerm()}");
        }
        return $this->md5;
    }
    
    function getTerm(){
        return $this->term;
    }
    
    function getTerms(){
        return explode(",", $this->term);
    }
    
    function getHours(){
        $hours = 0;
        foreach($this->getLines() as $line){
            if($line['hours'] != "N/A"){
                $hours += $line['hours'];
            }
        }
        return $hours;
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
    
    function getSupAccepted(){
        return $this->supAccepted;
    }
    
    function hasHQPAccepted(){
        return ($this->hqpAccepted != "0000-00-00 00:00:00");
    }
    
    function hasSupAccepted(){
        return ($this->supAccepted != "0000-00-00 00:00:00");
    }
    
    function isSupervisor($supId){
        return ($this->supId == $supId);
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

        $accepted = array();
        
        if($this->hasSupAccepted()){
            $accepted[$this->getSupervisor()->getId()] = "<p>Accepted by {$this->getSupervisor()->getFullName()}: <b>{$this->getSupAccepted()}</b></p>";
        }
        else {
            $accepted[$this->getSupervisor()->getId()] = "<p>Not yet accepted by {$this->getSupervisor()->getFullName()}</p>";
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
                <br />";
        $html .= "<div>
                    Graduate Assistantship Supervisor: <b>{$this->getSupervisor()->getFullName()}</b><br />
                    Period of Appointment: <b>{$start}</b> to <b>{$end}</b><br />
                </div><br />";
        foreach($this->getLines() as $line){
            $award = $line['award'];
            $salary = $line['salary'];
            $stipend = $line['stipend'];
            $hours = $line['hours'];
            if($hours == 0){
                $hours = "N/A";
            }
            $html .= "<div>
                Type of Appointment: <b>{$line['type']}</b><br />";
            if($line['type'] == "GTA" || $line['type'] == "GRA"){
                $html .= "Maximum Hours Assigned Per Week: <b>{$hours}</b><br />
                          Stipend Per Term: Award: <b>\$".number_format($award, 0)."</b> Salary: <b>\$".number_format($salary, 0)."</b> Total Stipend: <b>\$".number_format($stipend, 0)."</b>";
            }
            else if($line['type'] == "GRAF" || $line['type'] == "Fee Differential"){
                $html .= "Maximum Hours Assigned Per Week: <b>N/A</b><br />
                          Total Stipend: <b>\$".number_format(self::$GRAF_STIPEND*$line['hours']/12, 0)."</b>";
            }
            $html .="</div><br />";
        }
        $html .= "<ul type='a' style='margin-left: 1em;' >
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
    
    function getEmail(){
        global $wgServer, $wgScriptPath;
        $accounts = array();
        foreach($this->getLines() as $line){
            $accounts[] = $line['account'];
        }
        $start = date('F Y', strtotime($this->getStart()));
        $end = date('F Y', strtotime($this->getEnd()));
        $message = "<p>{$this->getSupervisor()->getFullName()} has filled out a contract</p>
            <div style='margin-left: 4em;'>
                for {$this->getHQP()->getFullName()}<br />
                to be funded by account ".implode(", ", $accounts)." held by {$this->getSupervisor()->getFullName()}<br />
                from {$start} to {$end} (".implode(", ", $this->getTerms()).").
            </div>
            <p>The PDF is attached, so review the terms and then <a href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?accept={$this->getMD5()}'><b>Click Here</b></a> to accept it</p>";
        return $message;
    }

}

?>
