<?php

class AlumniAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $alumni = Alumni::newFromId($this->getParam('id'));
            return $alumni->toJSON();
        }
        else if($this->getParam('personId') != "" ){
            $alumni = Alumni::newFromUserId($this->getParam('personId'));
            return $alumni->toJSON();
        }
    }
    
    function doPOST(){
        $alumni = new Alumni(array());
        $alumni->user_id = $this->getParam('personId');
        $alumni->recruited = $this->POST('recruited');
        $alumni->recruited_country = $this->POST('recruited_country');
        $alumni->alumni = $this->POST('alumni');
        $alumni->alumni_country = $this->POST('alumni_country');
        $alumni->alumni_sector = $this->POST('alumni_sector');
        $status = $alumni->create();
        if(!$status) {
            $this->throwError("Could not create Alumni");
        }
        return $alumni->toJSON();
    }
    
    function doPUT(){
        if($this->getParam('id') != ""){
            $alumni = Alumni::newFromId($this->getParam('id'));
        }
        else if($this->getParam('personId') != "" ){
            $alumni = Alumni::newFromUserId($this->getParam('personId'));
        }
        $alumni->recruited = $this->POST('recruited');
        $alumni->recruited_country = $this->POST('recruited_country');
        $alumni->alumni = $this->POST('alumni');
        $alumni->alumni_country = $this->POST('alumni_country');
        $alumni->alumni_sector = $this->POST('alumni_sector');
        $status = $alumni->update();
        if(!$status) {
            $this->throwError("Could not update Alumni");
        }
        return $alumni->toJSON();
    }
    
    function doDELETE(){
        if($this->getParam('id') != ""){
            $alumni = Alumni::newFromId($this->getParam('id'));
        }
        else if($this->getParam('personId') != "" ){
            $alumni = Alumni::newFromUserId($this->getParam('personId'));
        }
        $alumni = $alumni->delete();
        return $alumni->toJSON();
    }
	
}

?>
