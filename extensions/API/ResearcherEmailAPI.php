<?php

class ResearcherEmailAPI extends API{

    function __construct(){
        $this->addGET("name", false, "The User Name of the researcher to get.  The name must be in the format First.Last", "Eleni.Stroulia");
        $this->addGET("id", false, "The ID of the researcher to get", "3");
        $this->addGET("type", false, "The type of user to get.", NI);
        $this->addGET("format", false, "The format of the output(can either be 'xml', csv, or 'json').  If this value is not provided, then xml is assumed", "xml");
	}

  function processParams($params){
    if(isset($_GET['id'])){
      $_GET['name'] = $_GET['id'];
    }
    $i = 0;
	  foreach($params as $param){
	    if($i != 0){
	      if(strtoupper($param) == NI){
	        $_GET['type'] = NI;
	      }
	      else if(strtoupper($param) == HQP){
	        $_GET['type'] = HQP;
	      }
	      else if(strtoupper($param) == "XML"){
	        $_GET['format'] = "XML";
	      }
	      else if(strtoupper($param) == "JSON"){
	        $_GET['format'] = "JSON";
	      }
	      else if(strtoupper($param) == "CSV"){
	        $_GET['format'] = "CSV";
	      }
	      else if(!isset($_GET['name']) && is_numeric($param)){
	        $person = Person::newFromId($param);
	        if($person != null && $person->getName() != null){
	          $_GET['name'] = $person->getName();
	        }
	      }
	      else if(!isset($_GET['name'])){
	        $_GET['name'] = $param;
	      }
	    }
	    $i++;
	  }
  }

	function doAction(){
	    $start = microtime(true);
        if(!isset($_GET['format']) || $_GET['format'] == "XML"){
	        header("Content-type: text/xml");
	    }
	    else if($_GET['format'] == "JSON"){
	        header("Content-type: text/json");
	    }
	    else if($_GET['format'] == "CSV"){
	        @header("Content-disposition: attachment; filename='ResearcherEmail{$_GET['name']}{$_GET['id']}{$_GET['type']}.csv'");
	        header("Content-type: text/csv");
	    }
	    $cache = new ResearcherEmailCache($this);
		echo $cache->getCache();
		exit;
	}
	
	function outputXML($people){
	  global $wgScriptPath, $wgServer;
	  $xml = "<researchers>\n";
	  foreach($people as $person){
	    $name = $person->splitName();
	    $xml .= "\t<researcher type=\"{$person->getType()}\" id=\"{$person->getId()}\">\n";
	    $xml .= "\t\t<firstname>{$name['first']}</firstname>\n";
	    $xml .= "\t\t<lastname>{$name['last']}</lastname>\n";
	    $xml .= "\t\t<email>".str_replace(">", "&gt;", str_replace("<", "&lt;", $person->getEmail()))."</email>\n";
		$xml .= "\t</researcher>\n";
	  }
	  $xml .= "</researchers>\n";
      return $xml;
	}
	
	function outputCSV($people){
	    global $wgScriptPath, $wgServer;
	    $csv = "";
	    foreach($people as $person){
	        $name = $person->splitName();
	        $csv .= "\"{$name['first']}\", \"{$name['last']}\", \"{$person->getEmail()}\"\n";
	    }
        return $csv;
	}
	
	function outputJSON($people){
	  global $wgScriptPath, $wgServer;
	  $json = array();
	  foreach($people as $person){
	    $name = $person->splitName();
        $p = array("type" => $person->getType(),
                   "id" => $person->getId(),
                      "firstname" => $name['first'],
                      "lastname" => $name['last'],
                      "email" => $person->getEmail()
                     );
        $json[] = $p;
	  }
	  return json_encode($json);
	}
	
	function isLoginRequired(){
		return true;
	}
}

class ResearcherEmailCache extends SerializedCache{
    
    var $api;
    
    function ResearcherEmailCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "res");
    }
    
    function run(){
        $all = false;
		if(!isset($_GET['name'])){
			$all = true;
		}
		if($all){
            if(isset($_GET['type'])){
                $people = Person::getAllPeople($_GET['type']);
            }
            else {
		        $people = Person::getAllPeople();
		    }
		}
		else{
            $person = Person::newFromName($_GET['name']);
            if($person == null || $person->getName() == null){
                return "There is no user by the name of \"{$_GET['name']}\"";
            }
            $people = array($person);
        }
        // Determining what format to output
        if(isset($_GET['format']) && strtoupper($_GET['format']) == "JSON"){
            $output = $this->api->outputJSON($people);
        }
        else if(isset($_GET['format']) && strtoupper($_GET['format']) == "XML"){
            $output = $this->api->outputXML($people);
        }
        else if(isset($_GET['format']) && strtoupper($_GET['format']) == "CSV"){
            $output = $this->api->outputCSV($people);
        }
        else if(isset($_GET['format'])){
            return "Format \"{$_GET['format']}\" not supported";
        }
        else{
            $output = $this->api->outputXML($people);
        }
        return $output;
    }
}

?>
