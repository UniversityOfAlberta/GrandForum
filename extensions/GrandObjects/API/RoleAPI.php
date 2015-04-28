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
        $role = new Role(array());
        header('Content-Type: application/json');
        $role->user = $this->POST('userId');
        $role->role = $this->POST('name');
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
        $role = Role::newFromId($this->getParam('id'));
        if($role == null || $role->getRole() == ""){
            $this->throwError("This Role does not exist");
        }
        header('Content-Type: application/json');
        $role->role = $this->POST('name');
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
        $role = Role::newFromId($this->getParam('id'));
        if($role == null || $role->getRole() == ""){
            $this->throwError("This Role does not exist");
        }
        header('Content-Type: application/json');
        $status = $role->delete();
        $role->id = "";
        return $role->toJSON();
    }
	
}

?>
