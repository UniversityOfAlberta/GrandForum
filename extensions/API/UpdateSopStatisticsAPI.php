<?php

class UpdateSopStatisticsAPI extends API{

    function UpdateSopStatisticsAPI(){
    }

    function processParams($params){

    }

    function doAction($noEcho=false){
        $sop = SOP::newFromId(@$_GET['id']);
        $sop->updateStatistics();
        if(!$noEcho){
            echo "Sop updated\n";
        }
        return true;
   }

   function isLoginRequired(){
       return true;
   }
}
?>
