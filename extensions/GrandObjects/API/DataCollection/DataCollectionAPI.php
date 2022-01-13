<?php

class DataCollectionAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $data = DataCollection::newFromId($this->getParam('id'));
            if($data->getId() == 0){
                $this->throwError("Data Collection not found");
            }
            return $data->toJSON();
        }
        else if($this->getParam('personId') != "" && $this->getParam('page') != ""){
            $data = DataCollection::newFromUserId($this->getParam('personId'), 
                                                  $this->getParam('page'));
            if($data->getId() == 0){
                $this->throwError("Data Collection not found");
            }
            return $data->toJSON();
        }
        else{
            // Handle DataCollections?
            return json_encode(array());
        }
    }
    
    function doPOST(){
        $data = new DataCollection(array());
        if($data->canUserRead()){
            $data->page = $this->POST('page');
            $data->data = $this->POST('data');
            $data->create();
            return $data->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Data Collection");
        }
    }
    
    function doPUT(){
        $data = DataCollection::newFromId($this->getParam('id'));
        if($data->canUserRead()){
            $data->data = $this->POST('data');
            $data->update();
            return $data->toJSON();
        }
        else{
            $this->throwError("You are not allowed to update this Data Collection");
        }
    }
    
    function doDELETE(){
        $data = DataCollection::newFromId($this->getParam('id'));
        if($data->canUserRead()){
            $data->delete();
            return $data->toJSON();
        }
        else{
            $this->throwError("You are not allowed to delete this Data Collection");
        }
    }
	
}

?>
