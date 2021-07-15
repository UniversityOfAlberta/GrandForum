<?php

/**
 * @package GrandObjects
 */

class ElitePosting extends Posting {
    
    static $dbTable = 'grand_elite_postings';

    var $companyName;
    var $companyProfile;
    var $reportsTo;
    var $basedAt;
    var $responsibilities;
    var $qualifications;
    var $skills;
    var $comments;
    
    function ElitePosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->companyName = $row['company_name'];
            $this->companyProfile = $row['company_profile'];
            $this->reportsTo = $row['reports_to'];
            $this->basedAt = $row['based_at'];
            $this->responsibilities = $row['responsibilities'];
            $this->qualifications = $row['qualifications'];
            $this->skills = $row['skills'];
            $this->comments = $row['comments'];
        }
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($this->getVisibility() == "Accepted" || $this->getVisibility() == "Publish"){
            // Posting is Public
            return true;
        }
        if(($me->getId() == $this->getUserId() && !isset($_GET['apiKey'])) ||  
           ($me->isRoleAtLeast(STAFF) && $this->getPreviewCode() == @$_GET['previewCode']) ||
           ($me->isRoleAtLeast(STAFF) && !isset($_GET['apiKey']))){
            // Posting was created by the logged in user (or is Staff)
            return true;
        }
    }
    
    function getCompanyName(){
        return $this->companyName;
    }
    
    function getCompanyProfile(){
        return $this->companyProfile;
    }
    
    function getReportsTo(){
        return $this->reportsTo;
    }
    
    function getBasedAt(){
        return $this->basedAt;
    }
    
    function getResponsibilities(){
        return $this->responsibilities;
    }
    
    function getQualifications(){
        return $this->qualifications;
    }
    
    function getSkills(){
        return $this->skills;
    }
    
    function getComments(){
        return ($this->isAllowedToEdit()) ? $this->comments : "";
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['companyName'] = $this->getCompanyName();
        $json['companyProfile'] = $this->getCompanyProfile();
        $json['reportsTo'] = $this->getReportsTo();
        $json['basedAt'] = $this->getBasedAt();
        $json['responsibilities'] = $this->getResponsibilities();
        $json['qualifications'] = $this->getQualifications();
        $json['skills'] = $this->getSkills();
        $json['comments'] = $this->getComments();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('company_name' => $this->companyName,
                                                'company_profile' => $this->companyProfile,
                                                'reports_to' => $this->reportsTo,
                                                'based_at' => $this->basedAt,
                                                'responsibilities' => $this->responsibilities,
                                                'qualifications' => $this->qualifications,
                                                'skills' => $this->skills,
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
        }
        return $status;
    }
    
    function update(){
        $status = parent::update();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('company_name' => $this->companyName,
                                                'company_profile' => $this->companyProfile,
                                                'reports_to' => $this->reportsTo,
                                                'based_at' => $this->basedAt,
                                                'responsibilities' => $this->responsibilities,
                                                'qualifications' => $this->qualifications,
                                                'skills' => $this->skills,
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
        }
        return $status;
    }
}

?>
