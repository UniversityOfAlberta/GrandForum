<?php

class UserFrailtyIndexAPI extends API{

    function processParams($params){

    }

    function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0, $subItem=0){
        if ($userId === null) {
          $userId = $this->user_id;
        }
        $blb = new ReportBlob($blobType, $year, $userId, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, $subItem);
        $result = $blb->load($addr);
        $data = $blb->getData();

        return $data;
    }


    function doAction($noEcho=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang,$wgRequest,$wgOut, $wgMessage;
         //get user
         header("Content-type: text/json");

        $user = Person::newFromId($wgUser->getId());
        $user_id = $wgUser->getId();
        $tags = array();
	//get user answers for questions -- would have to get most recent survey TODO:change user
	//FEB22: CHANGING THIS FUNCTION TO GO WITH INTAKE SURVEY CHANGES
        $myJSON = json_encode($tags);
        echo $myJSON;
        exit;
    }

   function isLoginRequired(){
       return true;
   }
}
?>
