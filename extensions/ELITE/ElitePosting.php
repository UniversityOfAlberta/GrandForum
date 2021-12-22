<?php

/**
 * @package GrandObjects
 */

class ElitePosting extends Posting {
    
    static $dbTable = 'grand_elite_postings';

    var $type;
    var $extra = array();
    var $comments;
    var $previousVisibility = "";
    
    function ElitePosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->type = $row['type'];
            $this->comments = $row['comments'];
            $this->extra = json_decode($row['extra'], true);
            $this->previousVisibility = $this->visibility;
            if(!is_array($this->extra)){
                $this->extra = array();
            }
        }
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(EXTERNAL));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            return false;
        }
        if($this->getVisibility() == "Accepted" || $this->getVisibility() == "Publish"){
            // Posting is Public
            return true;
        }
        if($me->getId() == $this->getUserId() ||  
           $me->isRoleAtLeast(STAFF)){
            // Posting was created by the logged in user (or is Staff)
            return true;
        }
        return false;
    }
    
    function getType(){
        return $this->type;
    }
    
    /**
     * Returns the url of this Posting's page
     * @return string The url of this Posting's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        $class = get_class($this);
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:{$class}Page?page=".strtolower($this->getType())."#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:{$class}Page?page=".strtolower($this->getType())."&embed#/{$this->getId()}";
    }
    
    function getExtra($field=null){
        if($field == null){
            return $this->extra;
        }
        return @$this->extra[$field];
    }
    
    function getComments(){
        return ($this->isAllowedToEdit()) ? $this->comments : "";
    }
    
    function sendCreateMail(){
        global $config;
        $subject = "";
        $message = "";
        if($this->type == "Intern"){
            $subject = "ELITE Program for Black Youth - Confirmation of Receipt of Submission";
            $message = "Dear Internship Host,

Thank you for your project submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. This message confirms that we received a project application from you.

We will include your project in a list for selection by intern applicants. Should your project be selected by any candidates who are shortlisted, we will advise you. We highly encourage you to arrange and conduct interviews with those short-listed candidates who have been matched to your project.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the ELITE Program for Black Youth.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
Professor
Lead Editor, <i>Journal of Thermal Spray Technology</i>
Director, ELITE Program for Black Youth (www.eliteprogram.ca)
<i>for</i> 
ELITE Program for Black Youth
<a href='http://www.eliteprogram.ca'>www.eliteprogram.ca</a>
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>
<i>Work-integrated Training for Upward Mobility</i>
**************************************
Programme ELITE pour la Jeunesse Noire
<a href='http://www.eliteprogram.ca/fr/'>www.eliteprogram.ca/fr/</a>
<a href='http://www.eliteprogram.ca/fr/contactez-nous/'>www.eliteprogram.ca/fr/contactez-nous/</a>
<i>Formation intégrée au travail pour la mobilité ascendante</i>";
        }
        else if($this->type == "PhD"){
            $subject = "Engineering-ELITE-IBET PhD Fellowship - Confirmation of Receipt of Submission";
            $message = "Dear Supervisor,

Thank you for your submission to the Engineering-ELITE-IBET PhD Fellowship competition led by the Faculty of Engineering at the University of Alberta. This message confirms that we received a project application from you.

We will include your project in a list for selection by PhD Fellowship applicants who may not have already arranged for a PhD program supervisor or for those applicants who wish to learn more about other research programs in the Faculty. Should your project be selected by any candidates who are shortlisted, we will advise you. We highly encourage you to arrange and conduct interviews with those short-listed candidates who have been matched to your project.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the Engineering-ELITE-IBET PhD Fellowship Program.

With kind regards,

The Advisory and Nominating Committee
Faculty of Engineering, University of Alberta
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>";
        }
        if($message != ""){
            $message = nl2br($message);
            $headers  = "Content-type: text/html\r\n"; 
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($this->getUser()->getEmail(), $subject, $message, $headers);
        }
    }
    
    function sendMoreInfoMail(){
        global $config;
        $subject = "";
        $message = "";
        if($this->type == "Intern"){
            $subject = "ELITE Program for Black Youth - Request for Information on Submission";
            $message = "Dear Internship Host,
Thank you for your project submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. We are writing to request more information.

{$this->comments}

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the ELITE Program for Black Youth.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
Professor
Lead Editor, <i>Journal of Thermal Spray Technology</i>
Director, ELITE Program for Black Youth (www.eliteprogram.ca)
<i>for</i> 
ELITE Program for Black Youth
<a href='http://www.eliteprogram.ca'>www.eliteprogram.ca</a>
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>
<i>Work-integrated Training for Upward Mobility</i>
**************************************
Programme ELITE pour la Jeunesse Noire
<a href='http://www.eliteprogram.ca/fr/'>www.eliteprogram.ca/fr/</a>
<a href='http://www.eliteprogram.ca/fr/contactez-nous/'>www.eliteprogram.ca/fr/contactez-nous/</a>
<i>Formation intégrée au travail pour la mobilité ascendante</i>";
        }
        if($message != ""){
            $message = nl2br($message);
            $headers  = "Content-type: text/html\r\n"; 
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($this->getUser()->getEmail(), $subject, $message, $headers);
        }
    }
    
    function sendAcceptedMail(){
        global $config;
        $subject = "";
        $message = "";
        if($this->type == "Intern"){
            $subject = "ELITE Program for Black Youth - Confirmation of Acceptance of Submission";
            $message = "Dear Internship Host,

Thank you for your project submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. This message confirms that we approved your project application.

We will include your project in a list for selection by intern applicants. Should your project be selected by any candidates who are shortlisted, we will advise you. We highly encourage you to arrange and conduct interviews with those short-listed candidates who have been matched to your project.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the ELITE Program for Black Youth.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
Professor
Lead Editor, <i>Journal of Thermal Spray Technology</i>
Director, ELITE Program for Black Youth (www.eliteprogram.ca)
<i>for</i> 
ELITE Program for Black Youth
<a href='http://www.eliteprogram.ca'>www.eliteprogram.ca</a>
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>
<i>Work-integrated Training for Upward Mobility</i>
**************************************
Programme ELITE pour la Jeunesse Noire
<a href='http://www.eliteprogram.ca/fr/'>www.eliteprogram.ca/fr/</a>
<a href='http://www.eliteprogram.ca/fr/contactez-nous/'>www.eliteprogram.ca/fr/contactez-nous/</a>
<i>Formation intégrée au travail pour la mobilité ascendante</i>";
        }
        if($message != ""){
            $message = nl2br($message);
            $headers  = "Content-type: text/html\r\n"; 
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($this->getUser()->getEmail(), $subject, $message, $headers);
        }
    }
    
    function sendRejectedMail(){
        global $config;
        $subject = "";
        $message = "";
        if($this->type == "Intern"){
            $subject = "ELITE Program for Black Youth - Decision on Submission";
            $message = "Dear Internship Host,

Thank you for your project submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. Unfortunately, we were not able to approve your project application.

Please do not hesitate to contact us should you have any questions or wish to discuss the application further.

Thank you for your interest and continued support of the ELITE Program for Black Youth.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
Professor
Lead Editor, <i>Journal of Thermal Spray Technology</i>
Director, ELITE Program for Black Youth (www.eliteprogram.ca)
<i>for</i> 
ELITE Program for Black Youth
<a href='http://www.eliteprogram.ca'>www.eliteprogram.ca</a>
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>
<i>Work-integrated Training for Upward Mobility</i>
**************************************
Programme ELITE pour la Jeunesse Noire
<a href='http://www.eliteprogram.ca/fr/'>www.eliteprogram.ca/fr/</a>
<a href='http://www.eliteprogram.ca/fr/contactez-nous/'>www.eliteprogram.ca/fr/contactez-nous/</a>
<i>Formation intégrée au travail pour la mobilité ascendante</i>";
        }
        if($message != ""){
            $message = nl2br($message);
            $headers  = "Content-type: text/html\r\n"; 
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($this->getUser()->getEmail(), $subject, $message, $headers);
        }
    }
    
    function checkMatches(){
        if($this->type == "PhD"){
            // Only do this matching for PhD Projects
            $postings = DBFunctions::select(array('grand_elite_postings'),
                                            array('*'), 
                                            array('user_id' => $this->getUser()->getId(),
                                                  'type' => $this->type));
            if(count($postings) == 1){
                // Make sure only 1 project exists
                $posting = $postings[0];
                $other_projects = DBFunctions::select(array('grand_report_blobs'),
                                                      array('user_id', 'data'),
                                                      array('rp_type' => 'RP_PHD_ELITE',
                                                            'rp_section' => 'PROFILE',
                                                            'rp_item' => 'PROJECTS_OTHER'));
                foreach($other_projects as $other_project){
                    $applicantId = $other_project['user_id'];
                    $projects = unserialize($other_project['data']);
                    $projects = $projects['apply_other'];
                    foreach($projects as $project){
                        if(strtolower(trim($project['email'])) == strtolower(trim($this->getUser()->getEmail()))){
                            // Add this ElitePosting to the matched projects of the applicant
                            $blb = new ReportBlob(BLOB_ARRAY, 0, $applicantId, 0);
                            $addr = ReportBlob::create_address("RP_PHD_ELITE", 'PROFILE', 'PROJECTS', 0);
                            $result = $blb->load($addr);
                            $data = $blb->getData();
                            @$data['apply'][] = $posting['id'];
                            $blb->store($data, $addr);
                            
                            $blb = new ReportBlob(BLOB_ARRAY, 0, $applicantId, 0);
                            $addr = ReportBlob::create_address("RP_PHD_ELITE", 'PROFILE', 'MATCHES', 0);
                            $result = $blb->load($addr);
                            $data = $blb->getData();
                            @$data[] = $posting['id'];
                            $blb->store($data, $addr);
                        }
                    }
                }
            }
        }
    }
    
    function toSimpleArray(){
        $json = parent::toArray();
        $json['extra'] = $this->getExtra();
        return $json;
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['type'] = $this->getType();
        $json['extra'] = $this->getExtra();
        $json['comments'] = $this->getComments();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('type' => $this->type,
                                                'extra' => json_encode($this->extra),
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
            $this->sendCreateMail();
            $this->checkMatches();
        }
        return $status;
    }
    
    function update(){
        $status = parent::update();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('type' => $this->type,
                                                'extra' => json_encode($this->extra),
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
        }
        if($this->visibility == "Requested More Info"){
            $this->sendMoreInfoMail();
        }
        else if($this->visibility == "Accepted" && $this->previousVisibility != $this->visibility){
            $this->sendAcceptedMail();
        }
        else if($this->visibility == "Rejected" && $this->previousVisibility != $this->visibility){
            $this->sendRejectedMail();
        }
        return $status;
    }
}

?>
