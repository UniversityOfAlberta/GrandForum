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
	        $_POST['doi'] = preg_replace("/\\s/", "", $_POST['doi']);
	        $url = "http://dx.doi.org/{$_POST['doi']}";
	        $ch = curl_init();
	        $headers = array("Accept: application/x-bibtex");
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $_POST['bibtex'] = curl_exec($ch);
            if(strstr($_POST['bibtex'], "<title>Error: DOI Not Found</title>") !== false){
                $this->addError("DOI Not Found");
            }
            else{
                $_POST['bibtex'] = $_POST['bibtex']."\n";
                $searchFor = '}';
                $replaceWith = "\n}";
                $mainString = $_POST['bibtex'];
                
                $stringPosition = strrpos($mainString, $searchFor);
                $_POST['bibtex'] = substr_replace($mainString, $replaceWith, $stringPosition, strlen($searchFor));
                
                $api = new ImportBibTeXAPI();
                $res = $api->doAction(true);
                $this->messages = $api->messages;
                if($res === false){
                    $this->addError("No BibTeX references were found from this DOI");
                }
                else{
                    $this->data = $res;
                    return $res;
                }
            }
	    }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
