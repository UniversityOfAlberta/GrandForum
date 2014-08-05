<?php

class ImportDOIAPI extends API{

    function ImportDOIAPI(){
        $this->addPost("doi", true, "The doi reference", "10.1000/182");
    }

    function processParams($params){

    }
    
	function doAction($noEcho=false){
	    global $wgMessage;
	    if(isset($_POST['doi'])){
	        $url = "http://dx.doi.org/{$_POST['doi']}";
	        $ch = curl_init();
	        $headers = array("Accept: text/bibliography; style=bibtex");
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $_POST['bibtex'] = curl_exec($ch);
            $_POST['bibtex'] = $_POST['bibtex']."\n";
            return APIRequest::doAction('ImportBibTeX', true);
	    }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
