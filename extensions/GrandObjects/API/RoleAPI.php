<?php

class RoleAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $role = Role::newFromId($this->getParam('id'));
            return $role->toJSON();
        }
    }
    
    function doPOST(){
        return $this->doGet();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

?>
