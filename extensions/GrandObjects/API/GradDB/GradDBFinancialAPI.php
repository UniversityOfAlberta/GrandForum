<?php

class GradDBFinancialAPI extends RESTAPI {
    
    function doGET(){
        $hqpId = $this->getParam('hqpId');
        $year = $this->getParam('year');
        $hqp = Person::newFromId($hqpId);
        if($hqpId != "" && $hqp->getId() != 0 && $year != "" && is_numeric($year)){
            $scale = GradDBFinancial::getScale($hqp, $year);
            return json_encode($scale);
        }
        
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
