<?php

class BibliographyAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) == 1){
            $paper = Paper::newFromId($this->getParam('id'));
            if($paper == null || $paper->getTitle() == ""){
                $this->throwError("This product does not exist");
            }
            return $paper->toJSON();
        }
    }
    
    function doPOST(){
        $bib = new Bibliography(array());
        $bib->person = Person::newFromId($this->POST('person')->id);
        $bib->products = $this->POST('products');
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
