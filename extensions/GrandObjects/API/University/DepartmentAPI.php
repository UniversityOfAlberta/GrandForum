<?php

class DepartmentAPI extends RESTAPI {
    
    function doGET(){
        $depts = Person::getAllDepartments();
        return json_encode(array_filter(array_values($depts)));
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
