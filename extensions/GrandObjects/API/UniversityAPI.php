<?php

class UniversityAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        if($id != ""){
            $page = University::newFromId($id);
            return $page->toJSON();
        }
        else{
            $unis = new Collection(University::getAllUniversities());
            return $unis->toJSON();
        }
        return $page->toJSON();
    }
    
    function doPOST(){
        $uni = new University(array());
        $me = Person::newFromWgUser();
        $uni->setName($this->POST('name'));
        $uni->setShortName($this->POST('address'));
        $uni->setLatitude($this->POST('latitude'));
	    $uni->setLongitude($this->POST('longitude'));
	    $uni->setProvinceString($this->POST('province_string'));
        $extras = array();
        $extras['phone'] = $this->POST('phone');
        $extras['timeFrom'] = $this->POST('hour_from');
        $extras['timeTo'] =$this->POST('hour_to');
        $uni->setExtras($extras);
        $status = $uni->create();
        if(!$status){
            $this->throwError("There was an error");
        }
        $uni = University::newFromName($this->POST('name'));
        return $uni->toJSON();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

class UniversityNearestAPI extends RESTAPI {

    function doGET(){
        $lat = $this->getParam('lat');
	$long = $this->getParam('long');
        if(strpos($lat,"-") === false){
            $lat = "+".$lat;
        }
        if(strpos($long,"-") === false){
            $long = "+".$long;
        }
        $unis = new Collection(University::getNearestUniversity($lat,$long));
        return $unis->toJSON();
    }

    function doPOST(){
        return doGET();
    }

    function doPUT(){
        return doGET();
    }

    function doDELETE(){
        return doGET();
    }
}

?>
