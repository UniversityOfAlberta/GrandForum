<?php

class UniversityAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        $current = ($this->getParam('current') != "");
        if($id != ""){
            $page = University::newFromId($id);
            return $page->toJSON();
        }
        else{
            $unis = University::getAllUniversities();
            if($current){
                foreach($unis as $key => $uni){
                    $data = DBFunctions::execSQL("SELECT DISTINCT u.university_name
                                                  FROM `grand_universities` u, `grand_user_university` uu
                                                  WHERE u.university_id = uu.university_id
                                                  AND u.university_id = '{$uni->id}'
                                                  AND (CURRENT_TIMESTAMP BETWEEN uu.start_date AND uu.end_date OR
                                                       (CURRENT_TIMESTAMP >= uu.start_date AND uu.end_date = '0000-00-00 00:00:00'))");
                    if(count($data) == 0){
                        unset($unis[$key]);
                    }
                }
            }
            $unis = new Collection($unis);
            return $unis->toJSON();
        }
        return $page->toJSON();
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
