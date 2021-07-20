<?php

/**
 * @package GrandObjects
 */

class EliteProfile extends BackboneModel {
    
    var $person;
    var $status;
    var $data;
    
    static function newFromUserId($userId){
        
    }
    
    static function getAll(){
        DBFunctions::select(array('grand_pdf_report'),
                            array('user_id'),
                            array('type' => 'RPTP_ELITE'));
    }
    
    function EliteProfile(){
        
    }
    
    function toArray(){
        return array('status');
    }
    
    function create(){
    
    }
    
    function update(){
        
    }
    
    function delete(){
        
    }
    
    function exists(){
    
    }
    
    function getCacheId(){
        
    }
    
}

?>
