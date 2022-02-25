<?php

class AvoidResourceCategoryAPI extends RESTAPI {

    function doGET(){
        $cat = $this->getParam('cat');
        $res = new Collection(AvoidResource::getCategoryResources($cat));
        return $res->toJSON();
    }

    function doPOST(){
        return doGET();
    }

    function doPUT(){
        return doGET();
    }

    function doDELETE(){
        return doGET();
    }
}

?>
