<?php

class FreezeAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        if($id != ""){
            $freeze = Freeze::newFromId($id);
            return $freeze->toJSON();
        }
        else{
            $freezes = new Collection(Freeze::getAllFreezes());
            return $freezes->toJSON();
        }
    }
    
    function doPOST(){
        $freeze = new Freeze(array());
        $freeze->projectId = $this->POST('projectId');
        $freeze->feature = $this->POST('feature');
        $freeze->create();
        return $freeze->toJSON();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        $id = $this->getParam('id');
        $freeze = $freeze = Freeze::newFromId($id);
        $freeze->delete();
        return $freeze->toJSON();
    }
	
}

?>
