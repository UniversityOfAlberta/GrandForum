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
    var $training;
    var $responsibilities;
    var $qualifications;
    var $skills;
    var $level;
    var $positions;
    var $comments;
    
    function ElitePosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->companyName = $row['company_name'];
            $this->companyProfile = $row['company_profile'];
            $this->reportsTo = $row['reports_to'];
            $this->basedAt = $row['based_at'];
            $this->training = $row['training'];
            $this->responsibilities = $row['responsibilities'];
            $this->qualifications = $row['qualifications'];
            $this->skills = $row['skills'];
            $this->level = $row['level'];
            $this->positions = $row['positions'];
            $this->comments = $row['comments'];
        }
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(EXTERNAL));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($this->getVisibility() == "Accepted" || $this->getVisibility() == "Publish"){
            // Posting is Public
            return true;
        }
        if($me->getId() == $this->getUserId() ||  
           $me->isRoleAtLeast(STAFF)){
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
    
    function getTraining(){
        return $this->training;
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
    
    function getLevel(){
        return $this->level;
    }
    
    function getPositions(){
        return $this->positions;
    }
    
    function getComments(){
        return ($this->isAllowedToEdit()) ? $this->comments : "";
    }
    
    function toSimpleArray(){
        $json = parent::toArray();
        $json['companyName'] = $this->getCompanyName();
        return $json;
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['companyName'] = $this->getCompanyName();
        $json['companyProfile'] = $this->getCompanyProfile();
        $json['reportsTo'] = $this->getReportsTo();
        $json['basedAt'] = $this->getBasedAt();
        $json['training'] = $this->getTraining();
        $json['responsibilities'] = $this->getResponsibilities();
        $json['qualifications'] = $this->getQualifications();
        $json['skills'] = $this->getSkills();
        $json['level'] = $this->getLevel();
        $json['positions'] = $this->getPositions();
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
                                                'training' => $this->training,
                                                'responsibilities' => $this->responsibilities,
                                                'qualifications' => $this->qualifications,
                                                'skills' => $this->skills,
                                                'level' => $this->level,
                                                'positions' => $this->positions,
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
                                                'training' => $this->training,
                                                'responsibilities' => $this->responsibilities,
                                                'qualifications' => $this->qualifications,
                                                'skills' => $this->skills,
                                                'level' => $this->level,
                                                'positions' => $this->positions,
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
        }
        return $status;
    }
}

?>
