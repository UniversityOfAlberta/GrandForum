<?php

class PersonGsmsPdfTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonGsmsPdfTab($person, $visibility){
        parent::AbstractEditableTab("GSMS PDF");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
	$person = $this->person;
	$url = $person->getGSMSPdfUrl();
	if(!($url === false)){
            $this->html .= "<iframe width='800' height='500' frameborder='0' src='$url'></iframe>";
	} 
        return $this->html;
    }

    function generateEditBody(){
    }

    function handleEdit(){
}
    
    function canEdit(){
	return false;
    }
    
}
?>
