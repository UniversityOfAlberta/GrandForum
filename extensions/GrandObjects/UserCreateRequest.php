<?php

class UserCreateRequest {
    
    var $id;
    var $requestingUser;
    var $acceptedBy;
    var $name;
    var $realName;
    var $email;
    var $roles;
    var $projects;
    var $created;
    var $ignored;
    var $lastModified;
    
    static function getAllRequests($history=false){
        if($history){
		    $sql = "SELECT id
			        FROM `mw_user_create_request`
			        WHERE `created` = 'true'
			        OR `ignore` = 'true'
			        ORDER BY last_modified DESC";
		}
		else{
		    $sql = "SELECT id
			        FROM `mw_user_create_request`
			        WHERE `created` = 'false'
			        AND `ignore` = 'false'";
        }
        $data = DBFunctions::execSQL($sql);
        $requests = array();
        if(DBFunctions::getNRows() > 0){
            foreach($data as $row){
                $requests[] = UserCreateRequest::newFromId($row['id']);
            }
        }
        return $requests;
    }
    
    static function newFromId($id){
        $sql = "SELECT *
		        FROM `mw_user_create_request`
		        WHERE `id` = '{$id}'";
		$data = DBFunctions::execSQL($sql);
		return new UserCreateRequest($data);
    }
    
    function UserCreateRequest($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->requestingUser = Person::newFromName($data[0]['requesting_user']);
            $this->acceptedBy = Person::newFromName($data[0]['staff']);
            $this->name = $data[0]['wpName'];
            $this->realName = $data[0]['wpRealName'];
            $this->email = $data[0]['wpEmail'];
            $this->roles = $data[0]['wpUserType'];
            $this->projects = $data[0]['wpNS'];
            $this->created = ($data[0]['created'] == "true");
            $this->ignored = ($data[0]['ignore'] == "true");
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
    
    function getEmail(){
        return $this->email;
    }
    
    function getRoles(){
        return $this->roles;
    }
    
    function getProjects(){
        return $this->projects;
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
        $sql = "UPDATE `mw_user_create_request`
		        SET `last_modified` = SUBDATE(CURRENT_TIMESTAMP, INTERVAL 5 SECOND),
                    `staff` = '{$user->getName()}',
		            `ignore` = 'true'
		        WHERE `id` = '{$this->id}'";
		DBFunctions::execSQL($sql, true);
    }
    
    function acceptRequest(){
        global $wgUser;
        $user = Person::newFromUser($wgUser);
        $sql = "UPDATE `mw_user_create_request`
		        SET `last_modified` = SUBDATE(CURRENT_TIMESTAMP, INTERVAL 5 SECOND),
                    `staff` = '{$user->getName()}',
		            `created` = 'true'
		        WHERE `id` = '{$this->id}'";
	    DBFunctions::execSQL($sql, true);
    }
    
}

?>
