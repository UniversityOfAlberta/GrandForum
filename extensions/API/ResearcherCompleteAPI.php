<?php

class ResearcherCompleteAPI extends ResearcherAPI{

    function ResearcherCompleteAPI(){
        parent::ResearcherAPI();
	}

	function doAction(){
	    $start = microtime(true);
        if(!isset($_GET['format']) || $_GET['format'] == "XML"){
	        header("Content-type: text/xml");
	    }
	    else if($_GET['format'] == "JSON"){
	        header("Content-type: text/json");
	    }
	    $cache = new ResearcherCompleteFullCache($this);
		echo $cache->getCache();
		exit;
	}
	
	function outputXML($people){
	  global $wgScriptPath, $wgServer;
	  $xml = "<researchers>\n";
	  foreach($people as $person){
	    if(!$person->isRole(HQP)){
	      $uni = $person->getUniversity();
	      $name = $person->splitName();
	      $xml .= "\t<researcher type=\"{$person->getType()}\" id=\"{$person->getId()}\">\n";
	      $xml .= "\t\t<firstname>{$name['first']}</firstname>\n";
	      $xml .= "\t\t<lastname>{$name['last']}</lastname>\n";
	      if(file_exists("Photos/".str_ireplace(".", "_", $person->getName()).".jpg")){
	        $xml .= "\t\t<photo>$wgServer$wgScriptPath/Photos/".str_ireplace(".", "_", $person->getName()).".jpg</photo>\n";
	      }
	      if($uni != null){
	        $xml .= "\t\t<university>{$uni['university']}</university>\n";
	        $xml .= "\t\t<department>{$uni['department']}</department>\n";
	        $xml .= "\t\t<position>{$uni['position']}</position>\n";
	      }
	      $xml .= "\t\t<projects>\n";
	      foreach($person->getProjects() as $project){
	        $xml .= "\t\t\t<project id=\"{$project->getId()}\" name=\"{$project->getName()}\" />\n";
	      }
	      $xml .= "\t\t</projects>\n";
	      if($person->isRole(HQP)){
	        $xml .= "\t\t<supervisors>\n";
	        foreach($person->getCreators() as $supervisor){
	          $xml .= "\t\t\t<supervisor>{$supervisor->getName()}</supervisor>\n";
	        }
	        $xml .= "\t\t</supervisors>\n";
	      }
	      $xml .= "\t\t<biography>\n";
	      $xml .= "\t\t\t".str_replace("&", "&amp;", $person->getProfile())."\n";
	      $xml .= "\t\t</biography>\n";
	      $papers = $person->getPapers();
	      $publications = "";
	      $artifacts = "";
	      foreach($papers as $pub){
	        if($pub->getCategory() == "Publication"){
                $cache = new PublicationXMLCache($pub);
	            $publications .= $cache->getCache();
            }
            else if($pub->getCategory() == "Artifact"){
                $cache = new PublicationXMLCache($pub);
	            $artifacts .= $cache->getCache();
            }
	      }
	      $xml .= "\t\t<publications>$publications</publications>\n";
	      $xml .= "\t\t<artifacts>$artifacts</artifacts>\n";
	      $xml .= "\t</researcher>";
		  }
		}
		$xml .= "</researchers>\n";
		return $xml;
	}
	
	function outputJSON($people){
	  global $wgScriptPath, $wgServer;
	  $json = array();
	  foreach($people as $person){
	    if(!$person->isRole(HQP)){
	      $name = $person->splitName();
        $projects = array();
        $publications = array();
        $artifacts = array();
        foreach($person->getProjects() as $project){
          $projects[] = array("id" => $project->getId(),
                              "name" => $project->getName());
        }
        foreach($person->getPapers() as $pub){
            if($pub->getCategory() == "Publication"){
                $cache = new PublicationJSONCache($pub);
	            $publications[] = $cache->getCache();
            }
            else if($pub->getCategory() == "Artifact"){
                $cache = new PublicationJSONCache($pub);
	            $artifacts[] = $cache->getCache();
            }
        }
        $uni = $person->getUniversity();
        if($uni == null){
          $uni = array("university" => '',
                       "department" => '',
                       "position"   => '');
        }
        $p = array("type" => $person->getType(),
                   "id" => $person->getId(),
                      "firstname" => $name['first'],
                      "lastname" => $name['last'],
                      "university" => $uni['university'],
                      "department"  => $uni['department'],
                      "position"    => $uni['position'],
                      "projects" => $projects,
                      "biography" => $person->getProfile(),
                      "publications" => $publications,
                      "arifacts" => $artifacts
                     );
        if(file_exists("Photos/".str_ireplace(".", "_", $person->getName()).".jpg")){
	        $p["photo"] = "$wgServer$wgScriptPath/Photos/".str_ireplace(".", "_", $person->getName()).".jpg";
	      }
        if($person->isRole(HQP)){
          $supervisors = array();
	        foreach($person->getCreators() as $supervisor){
	          $supervisors[] = $supervisor->getName();
	        }
	        $p["supervisors"] = $supervisors;
	      }       
        $json[] = $p;
	    }
	  }
	  return json_encode($json);
	}
}

class ResearcherCompleteFullCache extends SerializedCache{
    
    var $api;
    
    function ResearcherCompleteFullCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "resC");
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
