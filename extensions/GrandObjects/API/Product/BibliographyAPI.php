<?php

class BibliographyAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $bib = Bibliography::newFromId($this->getParam('id'));
            return $bib->toJSON();
        }
        else if($this->getParam('person_id') != "" ){
            $person = Person::newFromId($this->getParam('person_id'));
            $bibs = new Collection($person->getBibliographies());
            return $bibs->toJSON();
        }
    }
    
    function doPOST(){
        $bib = new Bibliography(array());
        $bib->person = Person::newFromId($this->POST('person')->id);
        $bib->products = $this->POST('products');
        $bib->create();
        return $bib->toJSON();
    }
    
    function doPUT(){
        $bib = Bibliography::newFromId($this->getParam('id'));
        $bib->products = $this->POST('products');
        $bib = $bib->update();
        return $bib->toJSON();
    }
    
    function doDELETE(){
        $bib = Bibliography::newFromId($this->getParam('id'));
        $bib = $bib->delete();
        return $bib;
    }
	
}

?>
