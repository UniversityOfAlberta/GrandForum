<?php

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
