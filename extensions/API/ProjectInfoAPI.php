<?php

class ProjectInfoAPI extends API{

    function ProjectInfoAPI(){
        $this->addGET("name", false, "The Project Name", "MEOW");
        $this->addGET("id", false, "The Project ID", "172");
        $this->addGET("format", false, "The format of the output(can either be 'xml' or 'json').  If this value is not provided, then xml is assumed", "xml");
	}

  function processParams($params){
    if(isset($_GET['id'])){
      $_GET['name'] = $_GET['id'];
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
	      else if(!isset($_GET['name']) && is_numeric($param)){
	        $project = Project::newFromId($param);
	        if($project != null && $project->getName() != null){
	          $_GET['name'] = $project->getName();
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
	    global $queryCount;
        if(!isset($_GET['format']) || $_GET['format'] == "XML"){
	        header("Content-type: text/xml");
	    }
	    else if($_GET['format'] == "JSON"){
	        header("Content-type: text/json");
	    }
	    $cache = new ProjectFullCache($this);
		echo $cache->getCache();
		exit;
	}
	
	function outputXML($projects){
        global $wgScriptPath, $wgServer;
        $xml = "<projects>\n";
        foreach($projects as $project){
            $description = $project->getDescription();
            $leader = $project->getLeader();
            $coleader = $project->getCoLeader();
            $xml .= "\t<project id=\"{$project->getId()}\">\n";
            $xml .= "\t\t<name>{$project->getName()}</name>\n";
            if($leader != null){
                $uni = $leader->getUniversity();
                $name = $leader->splitName();
                $xml .= "\t\t<leader id=\"{$leader->getId()}\" firstname=\"{$name['first']}\" lastname=\"{$name['last']}\" type=\"{$leader->getType()}\" university=\"{$uni['university']}\" />\n";
            }
            if($coleader != null){
                $uni = $coleader->getUniversity();
                $name = $coleader->splitName();
                $xml .= "\t\t<coleader id=\"{$coleader->getId()}\" firstname=\"{$name['first']}\" lastname=\"{$name['last']}\" type=\"{$coleader->getType()}\" university=\"{$uni['university']}\" />\n";
            }
            $xml .= "\t\t<description>\n";
            $xml .= "\t\t\t".str_replace("&", "&amp;", $description)."\n";
            $xml .= "\t\t</description>\n";
            $coll = new Collection(Theme::getAllThemes(1));
            $themeNames = $coll->pluck('acronym');
            $xml .= "\t\t<themes>\n";
            $xml .= "\t\t\t<theme name=\"{$themeNames[0]}\" value=\"{$project->getTheme(1)}\" />\n";
            $xml .= "\t\t\t<theme name=\"{$themeNames[1]}\" value=\"{$project->getTheme(2)}\" />\n";
            $xml .= "\t\t\t<theme name=\"{$themeNames[2]}\" value=\"{$project->getTheme(3)}\" />\n";
            $xml .= "\t\t\t<theme name=\"{$themeNames[3]}\" value=\"{$project->getTheme(4)}\" />\n";
            $xml .= "\t\t\t<theme name=\"{$themeNames[4]}\" value=\"{$project->getTheme(5)}\" />\n";
            $xml .= "\t\t</themes>\n";
            $xml .= "\t\t<researchers>\n";
            foreach($project->getAllPeople() as $person){
                if(!$person->isHQP()){
                    $name = $person->splitName();
                    $uni = $person->getUniversity();
                    $xml .= "\t\t\t<researcher id=\"{$person->getId()}\" firstname=\"{$name['first']}\" lastname=\"{$name['last']}\" type=\"{$person->getType()}\" university=\"{$uni['university']}\" />\n";
                }
            }
            $xml .= "\t\t</researchers>\n";
            $xml .= "\t</project>";
        }
        $xml .= "</projects>\n";
        return $xml;
	}
	
	function outputJSON($projects){
	  global $wgScriptPath, $wgServer;
	  $json = array();
	  foreach($projects as $project){
	    $leader = $project->getLeader();
	    $coleader = $project->getCoLeader();
      $people = array();
      foreach($project->getAllPeople() as $person){
        if(!$person->isHQP()){
          $name = $person->splitName();
          $people[] = array("id" => $person->getId(), 
                          "firstname" => $name['first'], 
                          "lastname" => $name['last'],
                          "type" => $person->getType());
        }
      }
      $coll = new Collection(Theme::getAllThemes(1));
      $themeNames = $coll->pluck('acronym');
      $themes = array($themeNames[0] => $project->getTheme(1),
                      $themeNames[1] => $project->getTheme(2),
                      $themeNames[2] => $project->getTheme(3),
                      $themeNames[3] => $project->getTheme(4),
                      $themeNames[4] => $project->getTheme(5));
      $p = array("id" => $project->getId(),
                 "name" => $project->getName(),
                 "themes" => $themes,
                 "researchers" => $people);   
      if($leader != null){
	      $p["leader"] = "{$leader->getName()}"; 
	    }
	    if($coleader != null){
	      $p["coleader"] = "{$coleader->getName()}"; 
	    }
      $json[] = $p;
	  }
	  return json_encode($json);
	}
	
	function isLoginRequired(){
		return false;
	}
}

class ProjectFullCache extends SerializedCache{
    
    var $api;
    
    function ProjectFullCache($api){
        $this->api = $api;
        parent::SerializedCache(implode("", $_GET), "proj");
    }
    
    function run(){
        $all = false;
        if(!isset($_GET['name'])){
            $all = true;
        }
        if($all){
            $projects = Project::getAllProjects();
        }
        else{
            $project = Project::newFromName($_GET['name']);
            if($project == null || $project->getName() == null){
                return "There is no project by the name of \"{$_GET['name']}\"";
            }
            $projects = array($project);
        }
        // Determining what format to output
        if(isset($_GET['format']) && strtoupper($_GET['format']) == "JSON"){
            $output = $this->api->outputJSON($projects);
        }
        else if(isset($_GET['format']) && strtoupper($_GET['format']) == "XML"){
            $output = $this->api->outputXML($projects);
        }
        else if(isset($_GET['format'])){
            return "Format \"{$_GET['format']}\" not supported";
        }
        else{
            $output = $this->api->outputXML($projects);
        }
        return $output;
    }
}
?>
