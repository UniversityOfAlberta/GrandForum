<?php

class VirtuAPI extends RESTAPI {
    
    function doGET(){
        //if($this->getParam('max_products') != ""){
            $query = "SELECT MAX(num_products) as max_products FROM mw_virtu_experience";
            $data = DBFunctions::execSQL($query);

            $max_products = 0;
            if(isset($data[0]['max_products'])){
                $max_products = $data[0]['max_products'];
                
            }
            return json_encode(array("max_products"=>$max_products));
        //}
        
        //return "";
    }
    
    function doPOST(){
        return $this->doGet();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
    
}

?>
