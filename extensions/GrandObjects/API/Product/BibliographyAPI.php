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
        else{
            $bibs = new Collection(Bibliography::getAllBibliographies());
            return $bibs->toJSON();
        }
    }
    
    function doPOST(){
        $bib = new Bibliography(array());
        $bib->title = $this->POST('title');
        $bib->description = $this->POST('description');
        $bib->person = Person::newFromWgUser();
        $bib->editors = $this->POST('editors');
        $bib->products = $this->POST('products');
        $bib->thread_id = $this->POST('thread_id');
        $bib->create();
        return $bib->toJSON();
    }
    
    function doPUT(){
        $bib = Bibliography::newFromId($this->getParam('id'));
        $bib->title = $this->POST('title');
        $bib->description = $this->POST('description');
        $bib->editors = $this->POST('editors');
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
