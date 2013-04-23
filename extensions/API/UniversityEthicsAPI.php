<?php

class UniversityEthicsAPI extends API{

    function UniversityEthicsAPI(){
        //$this->addGET("id", false, "The Publication ID", "2401");
        //$this->addGET("user", false, "The name of an author.  The name must be in the format First.Last", "Martha.Ladly");
        //$this->addGET("project", false, "The name of a project", "GAMFIT");
        //$this->addGET("format", false, "The format of the output(can either be 'xml' or 'json').  If this value is not provided, then xml is assumed", "xml");
	}

    function processParams($params){
        
    }

	function doAction(){
	    //$start = microtime(true);
	    // if(!isset($_GET['format']) || $_GET['format'] == "XML"){
	    //     header("Content-type: text/xml");
	    // }
	    // else if($_GET['format'] == "JSON"){
	        header("Content-type: text/json");
	    //}
	    $cache = new UniversityEthicsFullCache($this);
		echo $cache->getCache();
		exit;
		
	}
	
    /*
	function outputXML($papers){
        global $wgScriptPath, $wgServer;
        $xml = "<publications>\n";
        foreach($papers as $paper){
	        $cache = new PublicationXMLCache($paper);
	        $xml .= $cache->getCache();
		}
		$xml .= "</publications>\n";
		return $xml;
	}
    */
	
	function outputJSON(){
	    global $wgScriptPath, $wgServer;
	    $json = array();
	   
	    $cache = new UniversityEthicsJSONCache();
	    $json = $cache->getCache();
	    
	    return json_encode($json);
	}
	
	function isLoginRequired(){
		return false;
	}
}

class UniversityEthicsFullCache extends SerializedCache{
    
    var $api;
    
    function UniversityEthicsFullCache($api){
        $this->api = $api;
        parent::SerializedCache("uniethics", "ethics");
    }
    
    function run(){
      
		$output = $this->api->outputJSON();
		
		return $output;
    }
}

class UniversityEthicsJSONCache extends SerializedCache{
    
    
    function UniversityEthicsJSONCache(){
        parent::SerializedCache("uniethics", "ethicsJSON");
       
    }
    
    function run(){
        
        $json = array();
        
        $hqps = Person::getAllPeople('HQP');
        $universities = array();

        foreach($hqps as $hqp){
            $uni = $hqp->getUni();
            $uni = ($uni == "")? "Unknown" : $uni;

            if(!array_key_exists($uni, $universities)){
                $universities[$uni] = array("ethical"=>0, "nonethical"=>0);
            }       
                
            $ethics = $hqp->getEthics();
            if($ethics['completed_tutorial'] == 1){
                $universities[$uni]['ethical']++;
            }
            else{
                $universities[$uni]['nonethical']++;
            }
        }

        foreach($universities as $uni => $stats){
            $ethical_num = $stats['ethical'];
            $total_num = $stats['ethical'] + $stats['nonethical'];
            if($total_num > 0){
                $percentage = ($ethical_num / $total_num)*100;
                $percentage = round($percentage, 1);
            }
            else{
                $percentage = 0;
            }

            $p = array(
                "university"=>$uni,
                "num_total"=>$total_num,
                "num_ethical"=>$ethical_num,
                "percent_ethical"=>$percentage
            );

            $json[] = $p;
            
        }

        return $json;
    }
}



?>
