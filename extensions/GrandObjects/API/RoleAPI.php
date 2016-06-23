<?php

class RoleAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $role = Role::newFromId($this->getParam('id'));
            return $role->toJSON();
        }
        else{
            $roles = Role::getDistinctRoles();
            return json_encode($roles);
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        $allowedRoles = $me->getAllowedRoles();
        if(!in_array($this->POST('name'), $allowedRoles)){
            $this->throwError("You are not allowed to add this person to that role");
        }
        $role = new Role(array());
        header('Content-Type: application/json');
        $role->user = $this->POST('userId');
        $role->role = $this->POST('name');
        $role->projects = $this->POST('projects');
        $role->startDate = $this->POST('startDate');
        $role->endDate = $this->POST('endDate');
        $role->comment = $this->POST('comment');
        $status = $role->create();
        if(!$status){
            $this->throwError("The role <i>{$role->getRole()}</i> could not be created");
        }
        $role = Role::newFromId($this->getParam('id'));
        return $role->toJSON();
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        $allowedRoles = $me->getAllowedRoles();
        $role = Role::newFromId($this->getParam('id'));
        if($role == null || $role->getRole() == ""){
            $this->throwError("This Role does not exist");
        }
        if(!in_array($this->POST('name'), $allowedRoles) || 
           !in_array($role->getRole(), $allowedRoles)){
            $this->throwError("You are not allowed to add this person to that role");
        }
        header('Content-Type: application/json');
        $role->role = $this->POST('name');
        $role->projects = $this->POST('projects');
        $role->startDate = $this->POST('startDate');
        $role->endDate = $this->POST('endDate');
        $role->comment = $this->POST('comment');
        $status = $role->update();
        if(!$status){
            $this->throwError("The role <i>{$role->getRole()}</i> could not be updated");
        }
        $role = Role::newFromId($this->getParam('id'));
        return $role->toJSON();
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        $allowedRoles = $me->getAllowedRoles();
        $role = Role::newFromId($this->getParam('id'));
        if($role == null || $role->getRole() == ""){
            $this->throwError("This Role does not exist");
        }
        if(!in_array($role->getRole(), $allowedRoles)){
            $this->throwError("You are not allowed to delete this role");
        }
        header('Content-Type: application/json');
        $status = $role->delete();
        $role->id = "";
        return $role->toJSON();
    }
	
}

?>
