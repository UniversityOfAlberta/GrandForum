<?php

/**
 * @package GrandObjects
 */

class UserCreateRequest {
    
    var $id;
    var $requestingUser;
    var $acceptedBy;
    var $name;
    var $realName;
    var $firstName;
    var $middleName;
    var $lastName;
    var $email;
    var $sendEmail;
    var $roles;
    var $subRoles;
    var $projects;
    var $relation;
    var $university;
    var $faculty;
    var $department;
    var $position;
    var $nationality;
    var $employment;
    var $recruitment;
    var $recruitmentCountry;
    var $startDate;
    var $endDate;
    var $candidate;
    var $created;
    var $ignored;
    var $lastModified;
    
    static function getAllRequests($history=false){
        if($history){
            $data = DBFunctions::select(array('grand_user_request'),
                                        array('id'),
                                        array('created' => EQ(1),
                                              WHERE_OR('`ignore`') => EQ(1)),
                                        array('last_modified' => 'DESC'));
		}
		else{
		    $data = DBFunctions::select(array('grand_user_request'),
		                                array('id'),
		                                array('created' => EQ(0),
		                                      '`ignore`' => EQ(0)));
        }
        $requests = array();
        if(count($data) > 0){
            foreach($data as $row){
                $requests[] = UserCreateRequest::newFromId($row['id']);
            }
        }
        return $requests;
    }
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_user_request'),
                                    array('*'),
                                    array('id' => EQ($id)));
		return new UserCreateRequest($data);
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->requestingUser = Person::newFromId($data[0]['requesting_user']);
            $this->acceptedBy = Person::newFromId($data[0]['staff']);
            $this->name = $data[0]['wpName'];
            $this->realName = $data[0]['wpRealName'];
            $this->firstName = $data[0]['wpFirstName'];
            $this->middleName = $data[0]['wpMiddleName'];
            $this->lastName = $data[0]['wpLastName'];
            $this->email = $data[0]['wpEmail'];
            $this->sendEmail = $data[0]['wpSendEmail'];
            $this->roles = $data[0]['wpUserType'];
            $this->subRoles = $data[0]['wpUserSubType'];
            $this->projects = $data[0]['wpNS'];
            $this->relation = $data[0]['relation'];
            $this->university = $data[0]['university'];
            $this->faculty = $data[0]['faculty'];
            $this->department = $data[0]['department'];
            $this->position = $data[0]['position'];
            $this->nationality = $data[0]['nationality'];
            $this->employment = $data[0]['employment'];
            $this->recruitment = $data[0]['recruitment'];
            $this->recruitmentCountry = $data[0]['recruitment_country'];
            $this->startDate = $data[0]['start_date'];
            $this->endDate = $data[0]['end_date'];
            $this->candidate = $data[0]['candidate'];
            $this->created = $data[0]['created'];
            $this->ignored = $data[0]['ignore'];
            $this->lastModified = ($data[0]['last_modified']);
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getRequestingUser(){
        return $this->requestingUser;
    }
    
    function getAcceptedBy(){
        return $this->acceptedBy;
    }
    
    function getName(){
        return $this->name;
    }
    
    function getRealName(){
        return $this->realName;
    }
    
    function getFirstName(){
        return $this->firstName;
    }
    
    function getMiddleName(){
        return $this->middleName;
    }
    
    function getLastName(){
        return $this->lastName;
    }
    
    function getEmail(){
        return $this->email;
    }
    
    function getSendEmail(){
        return $this->sendEmail;
    }
    
    function getRoles(){
        return $this->roles;
    }
    
    function getSubRoles(){
        return $this->subRoles;
    }
    
    function getProjects(){
        return $this->projects;
    }
    
    function getRelation(){
        return $this->relation;
    }
    
    function getUniversity(){
        return $this->university;
    }
    
    function getFaculty(){
        return $this->faculty;
    }
    
    function getDepartment(){
        return $this->department;
    }
    
    function getPosition(){
        return $this->position;
    }
    
    function getNationality(){
        return $this->nationality;
    }
    
    function getEmployment(){
        return $this->employment;
    }
    
    function getRecruitment(){
        return $this->recruitment;
    }
    
    function getRecruitmentCountry(){
        return $this->recruitmentCountry;
    }
    
    function getStartDate(){
        if($this->startDate == "0000-00-00 00:00:00"){
            return date('Y-m-d');
        }
        return $this->startDate;
    }
    
    function getEndDate(){
        return $this->endDate;
    }
    
    function getCandidate($transformed=false){
        if($transformed){
            if($this->candidate == 1) return "Yes";
            return "No";
        }
        return $this->candidate;
    }
    
    function isCreated(){
        return $this->created;
    }
    
    function isIgnored(){
        return $this->ignored;
    }
    
    function getLastModified(){
        return $this->lastModified;
    }
    
    function getCreatedUser(){
        if($this->isCreated()){
            return Person::newFromName($this->getName());
        }
        return null;
    }
    
    function ignoreRequest(){
        global $wgUser;
        $user = Person::newFromUser($wgUser);
        DBFunctions::update('grand_user_request',
                            array('last_modified' => EQ(COL('SUBDATE(CURRENT_TIMESTAMP, INTERVAL 5 SECOND)')),
                                  'staff' => $user->getId(),
                                  '`ignore`' => 1),
                            array('id' => $this->id));
    }
    
    function acceptRequest(){
        global $wgUser;
        $user = Person::newFromUser($wgUser);
        DBFunctions::update('grand_user_request',
                            array('last_modified' => EQ(COL('SUBDATE(CURRENT_TIMESTAMP, INTERVAL 5 SECOND)')),
                                  'staff' => $user->getId(),
                                  'created' => 1),
                            array('id' => $this->id));
    }
    
}

?>
