<?php

class BibliographyAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) == 1){
            $bib = Bibliography::newFromId($this->getParam('id'));
            return $bib->toJSON();
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
