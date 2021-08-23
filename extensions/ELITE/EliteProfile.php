<?php

/**
 * @package GrandObjects
 */

class EliteProfile extends BackboneModel {
    
    var $person;
    var $pdf;
    var $status;
    var $comments;
    var $projects = array();
    var $matches = array();
    
    static function newFromUserId($userId){
        $userId = DBFunctions::escape($userId);
        $data = DBFunctions::execSQL("SELECT user_id, report_id
                                      FROM grand_pdf_report
                                      WHERE type = 'RPTP_ELITE'
                                      AND user_id = '$userId'
                                      ORDER BY report_id DESC");
         $profile = new EliteProfile($data);
         if($profile->isAllowedToView()){
            return $profile;
         }
         return null;
    }
    
    static function getAllProfiles(){
        $data = DBFunctions::execSQL("SELECT t.user_id, t.report_id
                                      FROM (SELECT user_id, report_id
                                            FROM grand_pdf_report
                                            WHERE type = 'RPTP_ELITE'
                                            ORDER BY report_id DESC) t
                                      GROUP BY t.user_id");
        $profiles = array();
        foreach($data as $row){
            $profile = new EliteProfile(array($row));
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
                                      WHERE `rp_type` = 'RP_ELITE'
                                      AND `rp_section` = 'PROFILE'
                                      AND `rp_item` = 'MATCHES'");
        $matchedProfiles = array();
        foreach($data as $row){
            $userId = $row['user_id'];
            $matches = unserialize($row['data']);
            foreach($matches as $match){
                $posting = ElitePosting::newFromId($match);
                if($posting->getUserId() == $me->getId()){
                    $matchedProfiles[] = EliteProfile::newFromUserId($userId);
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
            $this->status = $this->getBlobValue('STATUS');
            $this->comments = $this->getBlobValue('COMMENTS');
            $projects = $this->getBlobValue('PROJECTS', BLOB_ARRAY);
            if($projects != null && isset($projects['apply'])){
                foreach($projects['apply'] as $proj){
                    $this->projects[] = ElitePosting::newFromId($proj);
                }
            }
            $matches = $this->getBlobValue('MATCHES', BLOB_ARRAY);
            if($matches != null){
                foreach($matches as $proj){
                    $this->matches[] = ElitePosting::newFromId($proj);
                }
            }
        }
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        // TODO: Need to also add 'HOSTs'
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
    }
    
    function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        // TODO: Need to also add 'HOSTs'
        if($me->getId() == $this->person->getId() ||
           $me->isRoleAtLeast(STAFF)){
            return true;
        }
    }
    
    function toArray(){
        $projects = array();
        foreach($this->projects as $project){
            $projects[] = $project->toSimpleArray();
        }
        $matches = array();
        foreach($this->matches as $project){
            $matches[] = $project->toSimpleArray();
        }
        return array('id' => $this->person->getId(),
                     'user' => $this->person->toSimpleArray(),
                     'status' => $this->status,
                     'comments' => $this->comments,
                     'projects' => $this->projects,
                     'matches' => $this->matches,
                     'pdf' => $this->pdf->getUrl(),
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
            $this->saveBlobValue('STATUS', $this->status);
            $this->saveBlobValue('COMMENTS', $this->comments);
            $this->saveBlobValue('MATCHES', $matches, BLOB_ARRAY);
        }
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
        $addr = ReportBlob::create_address('RP_ELITE', 'PROFILE', $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value, $blobType=BLOB_TEXT){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob($blobType, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_ELITE', 'PROFILE', $blobItem, 0);
        $blb->store($value, $addr);
    }
    
}

?>
