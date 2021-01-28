<?php

class PublicationByTitleAPI extends API{

    function __construct(){
        $this->addGET("name", true, "The title of the Publication", "Pub Title");
        $this->addGET("category", false, "The category of the Publication", "Artifact");
        $this->addGET("type", false, "The type of Publication", "Proceedings Paper");
        $this->addGET("status", false, "The status of the Publication", "Published");
	}

    function processParams($params){

    }

	function doAction(){
	    header("Content-type: text/json");
	    $matchedPapers = array();
	    $cat = (isset($_GET['category'])) ? $_GET['category'] : "all";
        $papers = Paper::getAllPapers('all', $cat, 'both');
        $altTitle = str_replace("'", "&#39;", $_GET['name']);
        foreach($papers as $paper){
            if((isset($_GET['name']) && ($paper->getTitle() == $_GET['name'] || $paper->getTitle() == $altTitle)) &&
               (!isset($_GET['category']) || $paper->getCategory() == $_GET['category']) &&
               (!isset($_GET['type']) || $paper->getType() == $_GET['type']) &&
               (!isset($_GET['status']) || $paper->getStatus() == $_GET['status'])){
                $matchedPapers[] = array('id' => $paper->getId(),
                                         'title' => $paper->getTitle(),
                                         'category' => $paper->getCategory(),
                                         'type' => $paper->getType(),
                                         'status' => $paper->getStatus(),
                                         'url' => $paper->getUrl());
            }
        }
        $this->addData("matched", $matchedPapers);
	}
	
	function outputJSON($papers){
	    global $wgScriptPath, $wgServer;
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

?>
