<?php

class HQPEpicTab2 extends HQPEpicTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct($person, $visibility);
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        // Only allow the user, supervisors, and STAFF+ to view the tab
        if($this->person->isEpic2()){
            return ($this->visibility['isMe'] || 
                    $this->visibility['isSupervisor'] ||
                    $me->isRoleAtLeast(SD) ||
                    $me->isEvaluatorOf($this->person, "RP_SUMMER", YEAR, "Person"));
        }
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(STAFF);
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$this->userCanView()){
            return "";
        }
        $dueDate = $this->getBlobValue('HQP_EPIC_REP_DATE');
        $this->html .= "<h3>Innovators of Tomorrow Certificate Program</h3>";
        $this->html .= "<p>The Innovators of Tomorrow Certificate Program consists of four online courses that align with the knowledge-based core domains, and always involve building skills in at least one of the six skills, identified in the framework you see here.</p>";
        if($this->person->isSubRole("Project Funded HQP") ||
           $this->person->isSubRole("Award HQP")){
            // IF HQP SUBROLE = AwardHQP; ProjectHQP
            $subrole = ($this->person->isSubRole("Project Funded HQP")) ? "Project Funded HQP" : "Award HQP";
            $this->html .= "<p>As a {$subrole}, you are required to earn the certificate within 1 calendar year of receiving AGE-WELL funds. Additional, optional, courses will be added to the course catalogue as they are developed - you are welcome to enroll and complete those at any time!</p>"; 
        }
        else if($this->person->isSubRole("SIP/CAT HQP") ||
                $this->person->isSubRole("Affiliate") ||
                $this->person->isSubRole("WP/CC Funded HQP")){
            // IF HQP SUBROLE = SIP/CAT; Affiliate; WP/CC Funded
            $subrole = ($this->person->isSubRole("SIP/CAT HQP")) ? "SIP/CAT HQP" : 
                       (($this->person->isSubRole("Affiliate")) ? "Affiliate" : "WP/CC Funded HQP");
            $this->html .= "<p>As a {$subrole}, you may choose to complete the certificate program in full. Additional, optional, courses will be added to the course catalogue as they are developed – you are welcome to enroll and complete those at any time!</p>";
        }
        $this->html .= "<img src='{$wgServer}{$wgScriptPath}/data/epic.png' style='height:250px;' />";
        $this->html .= "<h3 style='font-size:1.5em;'>EPIC Due Date: {$dueDate}</h3>";
        $this->html .= "<h3>HOW TO ACCESS AGE-WELL ONLINE COURSES</h3>";
        $this->html .= "<ol>
                            <li><b>Log-in to AGE-WELL Member's Intranet <a href='http://members.agewell-nce.ca/' target='_blank'>here</a>.</b> If you don’t have an Intranet account, please contact <a href='mailto:help@agewell-nce.ca'>help@agewell-nce.ca</a> with your Forum username and we will create an account for you. If you have an account but have forgotten your password, please go to <a href='http://members.agewell-nce.ca/password-reset/' target='_blank'>password reset page</a> and provide your Forum username and email address.</li>
                            <li><b>Enter the Course Portal:</b> Click on the Innovators of Tomorrow Certificate box (top right). Once on the landing page, you may enroll in the certificate program or access your Learner’s Dashboard to view and enroll in the optional courses we will develop over the coming year.</li>
                        </ol>";
        $this->html .= "<p><b>If you have any questions, please email <a href='mailto:training@agewell-nce.ca'>training@agewell-nce.ca</a></b></p>";
    }
    
    function generateEditBody(){
        $value = $this->getBlobValue('HQP_EPIC_REP_DATE');
        if($value == ""){
            $value = "Date not set";
        }
        $this->html .= "<b>EPIC Due Date:</b> <input type='text' value='{$value}' name='epic_HQP_EPIC_REP_DATE' />";
    }
    
}
?>
