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
        $contribution = new Contribution(array());
        $contribution->name = $this->POST('name');
        $contribution->people = $this->POST('people');
        $contribution->projects = $this->POST('projects');
        $contribution->partners = $this->POST('partners');
        $contribution->start_date = $this->POST('start');
        $contribution->end_date = $this->POST('end');
        $contribution->create();
        return $contribution->toJSON();
    }
    
    function doPUT(){
        if($this->getParam('rev_id') != ""){
            $contribution = Contribution::newFromRevId($this->getParam('rev_id'));
        } else {
            $contribution = Contribution::newFromId($this->getParam('id'));
        }
        $contribution->name = $this->POST('name');
        $contribution->people = $this->POST('people');
        $contribution->projects = $this->POST('projects');
        $contribution->partners = $this->POST('partners');
        $contribution->start_date = $this->POST('start');
        $contribution->end_date = $this->POST('end');
        $contribution->update();
        return $contribution->toJSON();
    }
    
    function doDELETE(){
        if($this->getParam('rev_id') != ""){
            $contribution = Contribution::newFromRevId($this->getParam('rev_id'));
        } else {
            $contribution = Contribution::newFromId($this->getParam('id'));
        }
        $contribution->delete();
        return $contribution->toJSON();
    }
	
}

?>
