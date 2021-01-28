<?php

class MaterialListAPI extends API{

    function __construct(){
        $this->addGET("type", false, "The type of the Material (can be either 'vimeo', 'youtube', 'img', 'video', 'audio', 'ppt', 'zip', 'pdf', 'other')", "img");
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
	    $cache = new MaterialListFullCache($this);
		echo $cache->getCache();
		exit;
	}
	
	function outputXML($materials){
        global $wgScriptPath, $wgServer;
        $xml = "<materials>\n";
        foreach($materials as $material){
            $xml .= "\t<material type=\"{$material->getHumanReadableType()}\" id=\"{$material->getId()}\" />\n";
		}
		$xml .= "</materials>\n";
		return $xml;
	}
	
    function outputJSON($materials){
        global $wgScriptPath, $wgServer;
        $json = array();
        foreach($materials as $material){
            $m = array("id" => $material->getId(),
                       "type" => $material->getType()
                      );    
            $json[] = $m;
        }
        return json_encode($json);
	}
	
	function isLoginRequired(){
		return false;
	}
}

class MaterialListFullCache extends SerializedCache{
    
    var $api;
    
    function MaterialListFullCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "matlist");
    }
    
    function run(){
	    if(isset($_GET['type'])){
            $materials =  Material::getAllMaterials($_GET['type']);
        }
        else {
	        $materials =  Material::getAllMaterials();
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
