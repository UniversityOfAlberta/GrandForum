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
    var $email;
    var $roles;
    var $projects;
    var $university;
    var $department;
    var $position;
    var $candidate;
    var $created;
    var $ignored;
    var $lastModified;
    var $extras;    
    var $certification;
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
                                        array('`ignore`' => EQ(0)));
        }
        $requests = array();
        if(count($data) > 0){
            foreach($data as $row){
                $request = UserCreateRequest::newFromId($row['id']);
                $person = Person::newFromName($request->getName());
                if($history){
                    $requests[] = $request;
                    
                }
                else{
                    if(!$request->isCreated() || $person->isCandidate()){
                        $requests[] = $request;
                    }
                }
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
    
    static function newFromName($name){
        $data = DBFunctions::select(array('grand_user_request'),
                                    array('*'),
                                    array('wpName' => EQ($name)));
        return new UserCreateRequest($data);
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->requestingUser = Person::newFromId($data[0]['requesting_user']);
            $this->acceptedBy = Person::newFromId($data[0]['staff']);
            $this->name = $data[0]['wpName'];
            $this->realName = $data[0]['wpRealName'];
            $this->email = $data[0]['wpEmail'];
            $this->roles = $data[0]['wpUserType'];
            $this->projects = $data[0]['wpNS'];
            $this->university = $data[0]['university'];
            $this->department = $data[0]['department'];
            $this->position = $data[0]['position'];
            $this->candidate = $data[0]['candidate'];
            $this->created = $data[0]['created'];
            $this->ignored = $data[0]['ignore'];
            $this->lastModified = ($data[0]['last_modified']);
            $this->extras = $data[0]['extras'];
            $this->certification = $data[0]['proof_certification'];
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
    
    function getPerson(){
        return Person::newFromName($this->name);
    }
    
    function getName(){
        return $this->name;
    }
    
    function getRealName(){
        return $this->realName;
    }
    
    function getEmail(){
        return $this->email;
    }
    
    function getRoles(){
        return $this->roles;
    }
    
    function getProjects(){
        return $this->projects;
    }
    
    function getUniversity(){
        return $this->university;
    }
    
    function getDepartment(){
        return $this->department;
    }
    
    function getPosition(){
        return $this->position;
    }
    
    function getCandidate($transformed=false){
        if($transformed){
            if($this->candidate == 1) return "Yes";
            return "No";
        }
        return $this->candidate;
    }

    function getExtras(){
        return unserialize($this->extras);
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
    
    function getCertification(){
        return @unserialize($this->certification);
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
    
    static function stream($action){
        $me = Person::newFromWgUser();
        if($action == "getCertification" && isset($_GET['id'])){
            $request = UserCreateRequest::newFromId($_GET['id']);
            if($me->isLoggedIn() && ($me->isRoleAtLeast(MANAGER) || $me->getName() == $request->getName())){
                $certification = $request->getCertification();
                header("Content-Disposition: attachment; filename={$request->getName()}");
                header("Content-Type: {$certification['file_data']['type']}");
                echo base64_decode($certification['file_data']['file']);
                exit;
            }
        }
        return true;
    }
    
}

?>
