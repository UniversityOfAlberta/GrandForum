<?php

class KeywordAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        $keywords = $this->getParam('keywords');
        $partners = $this->getParam('partners');
        if($keywords != ""){
            return json_encode(Keyword::getAllEnteredKeywords());
        }
        else if($partners != ""){
            return json_encode(Keyword::getAllEnteredPartners());
        }
        else if($id != ""){
            $grant = Keyword::newFromId($id);
            if($grant == null || $grant->getId() == 0){
                $this->throwError("This Keyword does not exist");
            }
            return $grant->toJSON();
        }
        else{
            $grants = new Collection(Keyword::getAllGrants());
            return $grants->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        $grant = new Keyword(array());
        $grant->owner_id = $me->getId();
        $grant->user_id = $this->POST('user_id');
        $grant->copi = $this->POST('copi');
        $grant->project_id = $this->POST('project_id');
        $grant->sponsor = $this->POST('sponsor');
        $grant->external_pi = $this->POST('external_pi');
        $grant->total = $this->POST('total');
        $grant->portions = array($me->getId() => str_replace(",", "", $this->POST('myportion')));
        $grant->funds_before = $this->POST('funds_before');
        $grant->funds_after = $this->POST('funds_after');
        $grant->keywords = $this->POST('keywords');
        $grant->partners = $this->POST('partners');
        $grant->title = $this->POST('title');
        $grant->scientific_title = $this->POST('scientific_title');
        $grant->description = $this->POST('description');
        $grant->role = $this->POST('role');
        $grant->seq_no = $this->POST('seq_no');
        $grant->prog_description = $this->POST('prog_description');
        $grant->request = $this->POST('request');
        $grant->start_date = $this->POST('start_date');
        $grant->end_date = $this->POST('end_date');
        $grant->create();
        return $grant->toJSON();
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        if($id != ""){
            $grant = Keyword::newFromId($id);
            if($grant == null || $grant->getId() == 0){
                $this->throwError("This Keyword does not exist");
            }
            $grant->user_id = $this->POST('user_id');
            $grant->copi = $this->POST('copi');
            $grant->project_id = $this->POST('project_id');
            $grant->sponsor = $this->POST('sponsor');
            $grant->external_pi = $this->POST('external_pi');
            $grant->total = $this->POST('total');
            $grant->portions[$me->getId()] = str_replace(",", "", $this->POST('myportion'));
            $grant->funds_before = $this->POST('funds_before');
            $grant->funds_after = $this->POST('funds_after');
            $grant->keywords = $this->POST('keywords');
            $grant->partners = $this->POST('partners');
            $grant->title = $this->POST('title');
            $grant->scientific_title = $this->POST('scientific_title');
            $grant->description = $this->POST('description');
            $grant->role = $this->POST('role');
            $grant->seq_no = $this->POST('seq_no');
            $grant->prog_description = $this->POST('prog_description');
            $grant->request = $this->POST('request');
            $grant->start_date = $this->POST('start_date');
            $grant->end_date = $this->POST('end_date');
            $grant->update();
            return $grant->toJSON();
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        $id = $this->getParam('id');
        if($id != ""){
            $grant = Keyword::newFromId($id);
            if($grant == null || $grant->getId() == 0){
                $this->throwError("This Keyword does not exist");
            }
            $grant->delete();
            return $grant->toJSON();
        }
        return $this->doGET();
    }
	
}

?>
