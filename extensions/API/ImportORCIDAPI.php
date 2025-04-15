<?php

class ImportORCIDAPI extends API{

    function __construct(){
        
    }

    function processParams($params){
        $_POST['project'] = @$_POST['project'];
    }
    
	function doAction($noEcho=false){
	    global $wgMessage;
	    if(isset($_COOKIE['access_token']) && isset($_COOKIE['orcid'])){
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, "https://api.orcid.org/v2.1/{$_COOKIE['orcid']}/works");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                        
                "Authorization: Bearer {$_COOKIE['access_token']}",
                "Accept: application/json"
            ));
            //execute post
            $result = json_decode(curl_exec($ch));
            
            //close connection
            curl_close($ch);
            $putcodes = array();
            $bibtex = "";
            if(isset($result->error) && $result->error == "invalid_token"){
                $this->addError("Invalid Access Token");
                return;
            }
            foreach($result->group as $key => $work){
                $putcodes[] = $work->{'work-summary'}[0]->{'put-code'};
                if(count($putcodes) == 100 || $key + 1 == count($result->group)){
                    $ch = curl_init();

                    //set the url, number of POST vars, POST data
                    curl_setopt($ch, CURLOPT_URL, "https://api.orcid.org/v2.1/{$_COOKIE['orcid']}/works/".implode(",", $putcodes));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                    
                        "Authorization: Bearer {$_COOKIE['access_token']}",
                        "Accept: application/json"
                    ));
                    //execute post
                    $workResults = json_decode(curl_exec($ch));
                    
                    //close connection
                    curl_close($ch);
                    foreach($workResults->bulk as $workResult){
                        if(isset($workResult->work->citation) && $workResult->work->citation->{'citation-type'} == "BIBTEX"){
                            $bibtex .= $workResult->work->citation->{'citation-value'}."\n";
                        }
                    }
                    
                    // Clear the array
                    $putcodes = array();
                }
            }
            $_POST['bibtex'] = $bibtex."\n";
            $api = new ImportBibTeXAPI();
            $res = $api->doAction(true);
            $this->messages = $api->messages;
            if($res === false){
                $this->addError("No BibTeX references were found from the ORCID import.");
            }
            else{
                $this->data = $res;
                return $res;
            }
        }
        else {
            $this->addError("You have not yet authorized your ORCID account.\n");
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
