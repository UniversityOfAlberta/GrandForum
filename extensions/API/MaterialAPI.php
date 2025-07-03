<?php

class MaterialAPI extends API{

    function __construct(){
        $this->addGET("title", false, "The title of the Material to get.", "My Material");
        $this->addGET("id", false, "The ID of the Material to get", "24");
        $this->addGET("type", false, "The type of the Material (can be either 'vimeo', 'youtube', 'img', 'video', 'audio', 'ppt', 'zip', 'pdf', 'other')", "img");
        $this->addGET("format", false, "The format of the output(can either be 'xml' or 'json').  If this value is not provided, then xml is assumed", "xml");
	}

  function processParams($params){
    if(isset($_GET['id'])){
      $_GET['title'] = $_GET['id'];
    }
    $i = 0;
	  foreach($params as $param){
	    if($i != 0){
	      if(strtoupper($param) == "XML"){
	        $_GET['format'] = "XML";
	      }
	      else if(strtoupper($param) == "JSON"){
	        $_GET['format'] = "JSON";
	      }
	      else if($param == "vimeo" || 
	              $param == "youtube" ||
	              $param == "img" ||
	              $param == "video" || 
	              $param == "audio" ||
	              $param == "ppt" ||
	              $param == "zip" || 
	              $param == "other" ||
	              $param == "pdf" || 
	              $param == "Other" ||
	              $param == "Image" ||
	              $param == "Video" ||
	              $param == "Audio" ||
	              $param == "Youtube Video" ||
	              $param == "Vimeo Video" ||
	              $param == "PDF Document" ||
	              $param == "Presentation" ||
	              $param == "Archive"){
	        $_GET['type'] = $param;
	      }
	      else if(!isset($_GET['title']) && is_numeric($param)){
	        $material = Material::newFromId($param);
	        if($material != null && $material->getTitle() != null){
	          $_GET['title'] = $material->getTitle();
	        }
	      }
	      else if(!isset($_GET['title'])){
	        $_GET['title'] = $param;
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
	    $cache = new MaterialFullCache($this);
		echo $cache->getCache();
		exit;
	}
	
	function outputXML($materials){
        global $wgScriptPath, $wgServer;
        $xml = "<materials>\n";
        foreach($materials as $material){
            $xml .= "\t<material type=\"{$material->getHumanReadableType()}\" id=\"{$material->getId()}\">\n";
            $xml .= "\t\t<title>".str_replace("&", "&amp;", $material->getTitle())."</title>\n";
            $xml .= "\t\t<date>".str_replace("&", "&amp;", $material->getDate())."</date>\n";
            $xml .= "\t\t<media><![CDATA[{$material->getMediaLink()}]]></media>\n";
            $xml .= "\t\t<url>".str_replace("&", "&amp;", $material->getUrl())."</url>\n";
            $xml .= "\t\t<people>\n";
            foreach($material->getPeople() as $person){
                $xml .= "\t\t\t<person id=\"{$person->getId()}\" name=\"{$person->getNameForForms()}\" />\n";
            }
            $xml .= "\t\t</people>\n";
            $xml .= "\t\t<projects>\n";
            foreach($material->getProjects() as $project){
                $xml .= "\t\t\t<project id=\"{$project->getId()}\" name=\"{$project->getName()}\" />\n";
            }
            $xml .= "\t\t</projects>\n";
            $xml .= "\t\t<description>".str_replace("&", "&amp;", $material->getDescription())."</description>\n";
            $xml .= "\t</material>\n";
		}
		$xml .= "</materials>\n";
		return $xml;
	}
	
    function outputJSON($materials){
        global $wgScriptPath, $wgServer;
        $json = array();
        foreach($materials as $material){
            $projects = array();
            $people = array();
            foreach($material->getProjects() as $project){
                $projects[] = array("id" => $project->getId(),
                                    "name" => $project->getName());
            }
            foreach($material->getPeople() as $person){
                $people[] = array("id" => $person->getId(),
                                  "name" => $person->getNameForForms());
            }
            $m = array("id" => $material->getId(),
                       "type" => $material->getType(),
                       "title" => $material->getTitle(),
                       "date" => $material->getDate(),
                       "media" => $material->getMediaLink(),
                       "url" => $material->getUrl(),
                       "projects" => $projects,
                       "people" => $people,
                       "description" => $material->getDescription()
                      ); 
            $json[] = $m;
        }
        return json_encode($json);
	}
	
	function isLoginRequired(){
		return false;
	}
}

class MaterialFullCache extends SerializedCache{
    
    var $api;
    
    function MaterialFullCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "mat");
    }
    
    function run(){
        $all = false;
		if(!isset($_GET['title'])){
			$all = true;
		}
		if($all){
		    if(isset($_GET['type'])){
                $materials =  Material::getAllMaterials($_GET['type']);
            }
            else {
		        $materials =  Material::getAllMaterials();
		    }
		}
		else{
            $material = Material::newFromTitle($_GET['title']);
            if($material == null || $material->getTitle() == null){
                return "There is no material by the title of \"{$_GET['title']}\"";
            }
            $materials = array($material);
        }
        // Determining what format to output
        if(isset($_GET['format']) && strtoupper($_GET['format']) == "JSON"){
            $output = $this->api->outputJSON($materials);
        }
        else if(isset($_GET['format']) && strtoupper($_GET['format']) == "XML"){
            $output = $this->api->outputXML($materials);
        }
        else if(isset($_GET['format'])){
            return "Format \"{$_GET['format']}\" not supported";
        }
        else{
            $output = $this->api->outputXML($materials);
        }
        return $output;
    }
}

?>
