<?php

class PublicationAPI extends API{

    function __construct(){
        $this->addGET("id", false, "The Publication ID", "2401");
        $this->addGET("user", false, "The name of an author.  The name must be in the format First.Last", "Martha.Ladly");
        $this->addGET("project", false, "The name of a project", "GAMFIT");
        $this->addGET("format", false, "The format of the output(can either be 'xml' or 'json').  If this value is not provided, then xml is assumed", "xml");
	}

    function processParams($params){
        $i = 0;
        foreach($params as $param){
            if($i != 0){
                if(strtoupper($param) == "XML"){
                    $_GET['format'] = "XML";
                }
                else if(strtoupper($param) == "JSON"){
                    $_GET['format'] = "JSON";
                }
                else if(!isset($_GET['id']) && is_numeric($param)){
                    $_GET['id'] = $param;
                }
                else if(!isset($_GET['project']) || !isset($_GET['user'])){
                    $project = Project::newFromName($param);
                    $person = Person::newFromName($param);
                    if($project != null && $project->getName() != null){
                        $_GET['project'] = $project->getName();
                    }
                    if($person != null && $person->getName() != null){
                        $_GET['user'] = $person->getName();
                    }
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
	    $cache = new PublicationFullCache($this);
		echo $cache->getCache();
		exit;
		/*
		$finish = microtime(true);
		
		$mem = memory_get_peak_usage(true);
		$bytes = array(1 => 'B', 2 => 'KiB', 3 => 'MiB', 4 => 'GiB');
		$ind = 1;
		while ($mem > 1024 && $ind < count($bytes)) {
			$mem = $mem / 1024;
			$ind++;
		}
		echo $mem."M | ".DBFunctions::getQueryCount()."queries | ".($finish - $start)."s";
		*/
	}
	
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
	
	function outputJSON($papers){
	    global $wgScriptPath, $wgServer;
	    ini_set('memory_limit','192M');
	    $json = array();
	    foreach($papers as $paper){
	        $cache = new PublicationJSONCache($paper);
	        $json[] = $cache->getCache();
	    }
	    return json_encode($json);
	}
	
	function isLoginRequired(){
		return false;
	}
}

class PublicationFullCache extends SerializedCache{
    
    var $api;
    
    function PublicationFullCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "pub");
    }
    
    function run(){
        $all = false;
        if(!isset($_GET['project']) && !isset($_GET['user']) && !isset($_GET['id'])){
			$all = true;
		}
		if($all){
			$papers = Paper::getAllPapers();
		}
		else if(isset($_GET['id'])){
		    $papers = array(Paper::newFromId($_GET['id']));
		}
		else if(isset($_GET['user'])){
		    $person = Person::newFromName($_GET['user']);
		    if($person == null || $person->getName() == null){
		        return "There is no user by the name of \"{$_GET['user']}\"";
		    }
		    $papers = $person->getPapers();
		}
		else if(isset($_GET['project'])){
		    $project = Project::newFromName($_GET['project']);
		    if($project == null || $project->getName() == null){
		        return "There is no project by the name of \"{$_GET['project']}\"";
		    }
		    $papers = $project->getPapers();
		}
		// Determining what format to output
		if(isset($_GET['format']) && strtoupper($_GET['format']) == "JSON"){
		    $output = $this->api->outputJSON($papers);
		}
		else if(isset($_GET['format']) && strtoupper($_GET['format']) == "XML"){
		    $output = $this->api->outputXML($papers);
		}
		else if(isset($_GET['format'])){
		    return "Format \"{$_GET['format']}\" not supported";
		}
		else{
		    $output = $this->api->outputXML($papers);
		}
		return $output;
    }
}

class PublicationJSONCache extends SerializedCache{
    
    var $paper;
    
    function PublicationJSONCache($paper){
        parent::SerializedCache($paper->getTitle(), "pubJSON");
        $this->paper = $paper;
    }
    
    function run(){
        $pAuthors = $this->paper->getAuthors();
        $json = array();
        if(count($pAuthors) > 0){
            $authors = array();
            $authorCount = 0;
            foreach($pAuthors as $author){
                $name = $author->splitName();
                $authors[] = array("id" => $author->getId(), 
                      "firstname" => $name['first'],
                      "lastname" => $name['last'], 
                      "type" => $author->getType());
                $authorCount++;
            }
            $projects = array();
            foreach($this->paper->getProjects() as $project){
                $projects[] = $project->getName();
            }
            if($authorCount > 0){
                $p = array("id" => $this->paper->getId(),
                     "type" => $this->paper->getType(),
                     "title" => $this->paper->getTitle(),
                     "authors" => $authors,
                     "date" => $this->paper->getDate(),
                     "venue" => $this->paper->getVenue(),
                     "projects" => $projects,
                     "data" => $this->paper->getData());
                $json[] = $p;
            }
        }
        return $json;
    }
}

class PublicationXMLCache extends SerializedCache{
	    
	var $paper;    
	    
    function PublicationXMLCache($paper){
        parent::SerializedCache($paper->getTitle(), "pubXML");
        $this->paper = $paper;
    }
    
    function run(){
        $xml = "";
        $authors = $this->paper->getAuthors();
        if(count($authors) > 0){
            $xml .= "\t<".strtolower($this->paper->getCategory())." id=\"{$this->paper->getId()}\" type=\"{$this->paper->getType()}\">\n";
            $xml .=  "\t\t<title>".str_replace("&amp;#39;", "&#39;", str_replace("&", "&amp;", $this->paper->getTitle()))."</title>\n";
            $xml .= "\t\t<authors>\n";
            foreach($authors as $author){
                $name = $author->splitName();
                $xml .= "\t\t\t<author id='{$author->getId()}' type='{$author->getType()}' firstname='{$name['first']}' lastname='{$name['last']}' />\n";
            }
            $xml .= "\t\t</authors>\n";
            $xml .= "\t<date>{$this->paper->getDate()}</date>\n";
            $xml .= "\t<venue>".str_replace("&", "&amp;", $this->paper->getVenue())."</venue>\n";
            $xml .= "\t<projects>\n";
            foreach($this->paper->getProjects() as $project){
                $xml .= "\t<project>{$project->getName()}</project>\n";
            }
            $xml .= "\t</projects>\n";
            $xml .= "\t<data>\n";
            foreach($this->paper->getData() as $attr => $value){
                if($value != ""){
                    $xml .= "\t\t<$attr>".str_replace("&amp;#39;", "&#39;", str_replace("&", "&amp;", $value))."</$attr>\n";
                }
            }
            $xml .= "\t</data>\n";
            $xml .= "\t</".strtolower($this->paper->getCategory()).">\n";
            return $xml;
        }
        return "";
    }
}

?>
