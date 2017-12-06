<?php

class ContributionAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            if($this->getParam('rev_id') != ""){
                $contribution = Contribution::newFromRevId($this->getParam('rev_id'));
            } else {
                $contribution = Contribution::newFromId($this->getParam('id'));
            }
            return $contribution->toJSON();
        }
        else{
            $contributions = new Collection(Contribution::getAllContributions());
            return $contributions->toJSON();
        }
    }
    
    function doPOST(){
        
    }
    
    function doPUT(){

    }
    
    function doDELETE(){

    }
	
}

?>
