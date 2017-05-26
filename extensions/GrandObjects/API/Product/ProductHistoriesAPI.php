<?php

class ProductHistoriesAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $productHistory = ProductHistory::newFromId($this->getParam('id'));
            if($productHistory == null || $productHistory->getId() == 0){
                $this->throwError("This product history does not exist");
            }
            return $productHistory->toJSON();
        }
        else if($this->getParam('personId') != ""){
            $json = array();
            $person = Person::newFromId($this->getParam('personId'));
            if($person == null || $person->getId() == 0){
                $this->throwError("This person does not exist");
            }
            $productHistories = new Collection($person->getProductHistories());
            return $productHistories->toJSON();
        }
    }
    
    function doPOST(){
        $productHistory = new ProductHistory();
        $productHistory->user_id = $this->POST('user_id');
        $productHistory->year = $this->POST('year');
        $productHistory->type = $this->POST('type');
        $productHistory->value = $this->POST('value');
        $productHistory->create();
        // TODO: Handle Errors
        return $productHistory->toJSON();
    }
    
    function doPUT(){
        if($this->getParam('id') != ""){
            $productHistory = ProductHistory::newFromId($this->getParam('id'));
            if($productHistory == null || $productHistory->getId() == 0){
                $this->throwError("This product history does not exist");
            }
            $productHistory->year = $this->POST('year');
            $productHistory->type = $this->POST('type');
            $productHistory->value = $this->POST('value');
            $productHistory->update();
            // TODO: Handle errors
            return $productHistory->toJSON();
        }
    }
    
    function doDELETE(){
        if($this->getParam('id') != ""){
            $productHistory = ProductHistory::newFromId($this->getParam('id'));
            if($productHistory == null || $productHistory->getId() == 0){
                $this->throwError("This product history does not exist");
            }
            $productHistory->delete();
            return $productHistory->toJSON();
        }
    }
	
}

?>
