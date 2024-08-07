<?php

/**
 * @package GrandObjects
 */

abstract class EliteProfile extends BackboneModel {
    
    var $person;
    var $pdf;
    var $region;
    var $status;
    var $comments;
    var $projects = array();
    var $otherProjects = array();
    var $matches = array();
    var $hires = array();
    
    static function newFromUserId($userId){
        $userId = DBFunctions::escape($userId);
        $data = DBFunctions::execSQL("SELECT user_id, report_id
                                      FROM grand_pdf_report
                                      WHERE type = 'RPTP_".static::$rpType."'
                                      AND user_id = '$userId'
                                      ORDER BY report_id DESC");
         $profile = new static($data);
         if($profile->isAllowedToView()){
            return $profile;
         }
         return null;
    }
    
    static function getAllProfiles(){
        $data = DBFunctions::execSQL("SELECT t.user_id, MAX(t.report_id) as report_id
                                      FROM (SELECT user_id, report_id
                                            FROM grand_pdf_report
                                            WHERE type = 'RPTP_".static::$rpType."'
                                            ORDER BY report_id DESC) t
                                      GROUP BY t.user_id");
        $profiles = array();
        foreach($data as $row){
            $profile = new static(array($row));
            if($profile->isAllowedToView()){
                $profiles[] = $profile;
            }
        }
        return $profiles;
    }
    
    static function getAllMatchedProfiles(){
        $me = Person::newFromWgUser();
        $data = DBFunctions::execSQL("SELECT * 
                                      FROM `grand_report_blobs`
                                      WHERE `rp_type` = 'RP_".static::$rpType."'
                                      AND `rp_section` = 'PROFILE'
                                      AND `rp_item` = 'MATCHES'
                                      AND user_id IN (SELECT user_id FROM grand_pdf_report WHERE type = 'RPTP_".static::$rpType."')");
        $matchedProfiles = array();
        foreach($data as $row){
            $userId = $row['user_id'];
            $matches = unserialize($row['data']);
            foreach($matches as $match){
                $posting = ElitePosting::newFromId($match);
                if($posting->getUserId() == $me->getId()){
                    $matchedProfiles[] = static::newFromUserId($userId);
                    break;
                }
            }
        }
        return $matchedProfiles;
    }
    
    function EliteProfile($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->person = Person::newFromId($row['user_id']);
            $this->pdf = PDF::newFromId($row['report_id']);
            $this->region = $this->getBlobValue('REGION');
            $this->status = $this->getBlobValue('STATUS');
            $this->comments = $this->getBlobValue('ADMIN_COMMENTS');
            $projects = $this->getBlobValue('PROJECTS', BLOB_ARRAY);
            $otherProjects1 = $this->getBlobValue('PROJECTS_OTHER', BLOB_ARRAY);
            $otherProjects2 = $this->getBlobValue('PROJECTS_OTHER2', BLOB_ARRAY);
            if($projects != null && isset($projects['apply'])){
                foreach($projects['apply'] as $proj){
                    $this->projects[] = ElitePosting::newFromId($proj);
                }
            }
            if($otherProjects1 != null && isset($otherProjects1['apply_other'])){
                foreach($otherProjects1['apply_other'] as $proj){
                    $this->otherProjects[] = array('name' => $proj['name'], 'email' => $proj['email']);
                }
            }
            if($otherProjects2 != null && isset($otherProjects2['apply_other2'])){
                foreach($otherProjects2['apply_other2'] as $proj){
                    $this->otherProjects[] = array('name' => $proj['name'], 'email' => '');
                }
            }
            $matches = $this->getBlobValue('MATCHES', BLOB_ARRAY);
            if($matches != null){
                foreach($matches as $proj){
                    $this->matches[] = ElitePosting::newFromId($proj);
                }
            }
            $hires = $this->getBlobValue('HIRES', BLOB_ARRAY);
            if($hires != null){
                $this->hires = $hires;
            }
        }
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            return false;
        }
        if($me->getId() == $this->person->getId() ||
           $me->isRoleAtLeast(STAFF)){
            return true;
        }
        if($me->isRole(EXTERNAL)){
            foreach($this->matches as $match){
                if($match->getUserId() == $me->getId()){
                    return true;
                }
            }
        }
        return false;
    }
    
    function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        if($me->getId() == $this->person->getId() ||
           $me->isRoleAtLeast(STAFF)){
            return true;
        }
    }
    
    function getReferenceLetters(){
        global $wgServer, $wgScriptPath;
        $md5s = array();
        // First check letters uploaded by the candidate
        $letter1 = DBFunctions::select(array('grand_report_blobs'),
                                       array('md5'),
                                       array('year' => 0,
                                             'user_id' => $this->person->getId(),
                                             'rp_type' => "RP_".static::$rpType,
                                             'rp_section' => 'PROFILE',
                                             'rp_item' => 'LETTER1'));
        $letter2 = DBFunctions::select(array('grand_report_blobs'),
                                       array('md5'),
                                       array('year' => 0,
                                             'user_id' => $this->person->getId(),
                                             'rp_type' => "RP_".static::$rpType,
                                             'rp_section' => 'PROFILE',
                                             'rp_item' => 'LETTER2'));
        if(count($letter1) > 0){
            $md5s[] = $letter1[0]['md5'];
        }
        if(count($letter2) > 0){
            $md5s[] = $letter2[0]['md5'];
        }
        // Check Other reference letters uploaded by the references themselves
        $other_letters = $this->getBlobValue('LETTER_OTHER', BLOB_ARRAY);
        if(is_array($other_letters)){
            foreach($other_letters['letter_other'] as $letter){
                $email = trim($letter['email']);
                $id = trim($letter['id']);
                $md5 = md5("{$email}:{$id}");
                $reference = DBFunctions::select(array('grand_report_blobs'),
                                                 array('md5'),
                                                 array('year' => 0,
                                                       'user_id' => $this->person->getId(),
                                                       'rp_type' => "RP_".static::$rpType,
                                                       'rp_section' => 'PROFILE',
                                                       'rp_item' => 'LETTER',
                                                       'rp_subitem' => $md5));
                if(count($reference) > 0){
                    $md5s[] = $reference[0]['md5'];
                }
            }
        }
        $urls = array();
        foreach($md5s as $md5){
            $md5 = urlencode(encrypt($md5));
            $urls[] = "{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}";
        }
        return $urls;
    }
    
    abstract function acceptedMessage();
    
    abstract function shortlistMessage();
    
    abstract function moreInfoMessage();
    
    abstract function rejectedMessage();
    
    abstract function receivedMessage();
    
    abstract function sendMatchedMail($person);
    
    abstract function sendHiresMail($person);
    
    function sendMail(){
        global $config, $wgAdditionalMailParams;
        $subject = "";
        $message = "";
        $to = $this->person->getEmail();
        if($this->status == "Accepted"){
            list($subject, $message) = $this->acceptedMessage();
            $matches = $this->getBlobValue('MATCHES', BLOB_ARRAY);
            foreach($this->hires as $key => $hire){
                if($hire == "Accepted" && in_array($key, $matches)){
                    $posting = ElitePosting::newFromId($key);
                    $host = $posting->getUser();
                    $to .= ",{$host->getEmail()}";
                }
            }
        }
        else if($this->status == "Shortlist"){
            list($subject, $message) = $this->shortlistMessage();
        }
        else if($this->status == "Requested More Info"){
            list($subject, $message) = $this->moreInfoMessage();
        }
        else if($this->status == "Rejected"){
            list($subject, $message) = $this->rejectedMessage();
        }
        else if($this->status == "Received"){
            list($subject, $message) = $this->receivedMessage();
        }
        if($subject != "" && $message != ""){
            $eol = "\r\n";
            $uid = md5(uniqid(time()));
            $msg = nl2br($message);
            
            // Basic headers
            $headers = "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>".$eol;
            $headers .= "MIME-Version: 1.0".$eol;
            $headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"".$eol;
            
            // Put everything else in $message
            $message = "--".$uid.$eol;
            $message .= "Content-Type: text/html; charset=utf-8".$eol;
            $message .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
            $message .= $msg.$eol.$eol;
            
            if(isset($_POST['file']) && $_POST['file'] != ""){
                $eol = "\r\n";
                $fileObj = $_POST['file'];
                $exploded = explode(",", $fileObj->data);
                $message .= "--".$uid.$eol;
                $message .= "Content-Type: application/octet-stream; name=\"".$fileObj->filename."\"".$eol;
                $message .= "Content-Transfer-Encoding: base64".$eol;
                $message .= "Content-Disposition: attachment; filename=\"".$fileObj->filename."\"".$eol.$eol;
                $message .= @chunk_split($exploded[1]).$eol.$eol;
            }
            $message .= "--".$uid."--";
            mail($to, $subject, $message, $headers, $wgAdditionalMailParams);
        }
    }
    
    function toArray(){
        return array('id' => $this->person->getId(),
                     'user' => $this->person->toSimpleArray(),
                     'region' => $this->region,
                     'status' => $this->status,
                     'comments' => $this->comments,
                     'projects' => $this->projects,
                     'otherProjects' => $this->otherProjects,
                     'matches' => $this->matches,
                     'hires' => $this->hires,
                     'pdf' => $this->pdf->getUrl(),
                     'letters' => $this->getReferenceLetters(),
                     'created' => $this->pdf->getTimestamp());
    }
    
    function create(){
        if($this->isAllowedToEdit()){
            return $this->update();
        }
    }
    
    function update(){
        if($this->isAllowedToEdit()){
            $matches = array();
            foreach($this->matches as $match){
                if($match instanceof Posting){
                    $matches[] = $match->getId();
                }
                else if(is_object($match)){
                    $matches[] = $match->id;
                }
                else{
                    $matches[] = $match;
                }
            }
            $oldStatus = $this->getBlobValue('STATUS');
            $oldMatches = $this->getBlobValue('MATCHES', BLOB_ARRAY);
            $this->saveBlobValue('STATUS', $this->status);
            $this->saveBlobValue('ADMIN_COMMENTS', $this->comments);
            $this->saveBlobValue('MATCHES', $matches, BLOB_ARRAY);
            foreach($matches as $match){
                if(is_array($oldMatches) && !in_array($match, $oldMatches)){
                    $posting = ElitePosting::newFromId($match);
                    $person = $posting->getUser();
                    $this->sendMatchedMail($person);
                }
            }
            if($oldStatus != $this->status){
                // Need to send an email
                $this->sendMail();
            }
        }
    }
    
    function updateHires(){
        $me = Person::newFromWgUser();
        $this->saveBlobValue('HIRES', $this->hires, BLOB_ARRAY);
        $this->sendHiresMail($me);
    }
    
    function delete(){
        if($this->isAllowedToEdit()){
            
        }
    }
    
    function exists(){
        return ($this->person != null);
    }
    
    function getCacheId(){
        
    }
    
    function getBlobValue($blobItem, $blobType=BLOB_TEXT){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob($blobType, $year, $personId, $projectId);
        $addr = ReportBlob::create_address("RP_".static::$rpType, 'PROFILE', $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value, $blobType=BLOB_TEXT){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob($blobType, $year, $personId, $projectId);
        $addr = ReportBlob::create_address("RP_".static::$rpType, 'PROFILE', $blobItem, 0);
        $blb->store($value, $addr);
    }
    
}

?>
