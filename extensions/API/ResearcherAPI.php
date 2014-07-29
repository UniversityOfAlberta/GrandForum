<?php

class ResearcherAPI extends API{

    function ResearcherAPI(){
        $this->addGET("name", false, "The User Name of the researcher to get.  The name must be in the format First.Last", "Eleni.Stroulia");
        $this->addGET("id", false, "The ID of the researcher to get", "3");
        $this->addGET("type", false, "The type of user to get.", PNI);
        $this->addGET("format", false, "The format of the output(can either be 'xml' or 'json').  If this value is not provided, then xml is assumed", "xml");
    }

  function processParams($params){
    if(isset($_GET['id'])){
      $_GET['name'] = $_GET['id'];
    }
    $i = 0;
      foreach($params as $param){
        if($i != 0){
          if(strtoupper($param) == PNI){
            $_GET['type'] = PNI;
          }
          else if(strtoupper($param) == CNI){
            $_GET['type'] = CNI;
          }
          else if(strtoupper($param) == "XML"){
            $_GET['format'] = "XML";
          }
          else if(strtoupper($param) == "JSON"){
            $_GET['format'] = "JSON";
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
            header("Content-type: application/json");
        }
        $cache = new ResearcherFullCache($this);
        echo $cache->getCache();
        exit;
    }
    
    function outputXML($people){
      global $wgScriptPath, $wgServer;
      $xml = "<researchers>\n";
      foreach($people as $person){
        if(!$person->isHQP()){
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
            if($project->getPhase() == PROJECT_PHASE && !$project->isSubProject()){
                $xml .= "\t\t\t<project id=\"{$project->getId()}\" name=\"{$project->getName()}\" />\n";
            }
          }
          $xml .= "\t\t</projects>\n";
          if($person->isHQP()){
            $xml .= "\t\t<supervisors>\n";
            foreach($person->getCreators() as $supervisor){
              $xml .= "\t\t\t<supervisor>{$supervisor->getName()}</supervisor>\n";
            }
            $xml .= "\t\t</supervisors>\n";
          }
          $xml .= "\t\t<biography>\n";
          $xml .= "\t\t\t".str_replace("&", "&amp;", $person->getProfile())."\n";
          $xml .= "\t\t</biography>\n";
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
        if(!$person->isHQP()){
          $name = $person->splitName();
        $projects = array();
        foreach($person->getProjects() as $project){
            if($project->getPhase() == PROJECT_PHASE && !$project->isSubProject()){
              $projects[] = array("id" => $project->getId(),
                                  "name" => $project->getName());
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
                      "biography" => $person->getBiography()
                     );
        if(file_exists("Photos/".str_ireplace(".", "_", $person->getName()).".jpg")){
            $p["photo"] = "$wgServer$wgScriptPath/Photos/".str_ireplace(".", "_", $person->getName()).".jpg";
          }
        if($person->isHQP()){
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
    
    function isLoginRequired(){
        return false;
    }
}

class ResearcherFullCache extends SerializedCache{
    
    var $api;
    
    function ResearcherFullCache($api){
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
