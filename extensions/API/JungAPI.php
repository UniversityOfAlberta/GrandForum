<?php

class JungAPI extends API{

    var $year = 2012;
    var $startDate = REPORTING_END;
    var $endDate = REPORTING_END;

    function JungAPI(){
        $this->addGET("nodeType", true, "", "all");
        $this->addGET("edgeType", true, "", "all");
        $this->addGET("year", true, "", "2012");
	}

    function processParams($params){
        $_GET['nodeType'] = mysql_real_escape_string($_GET['nodeType']);
        $_GET['edgeType'] = mysql_real_escape_string($_GET['edgeType']);
        $_GET['year'] = mysql_real_escape_string($_GET['year']);
    }

	function doAction(){
	    header("Content-type: application/json");
	    $this->outputJSON();
		exit;
	}
	
	function outputJSON(){
        $json = array();
        $nodeType = $_GET['nodeType'];
        $edgeType = $_GET['edgeType'];
        $this->year = $_GET['year'];
        $this->startDate = $_GET['year'].REPORTING_CYCLE_END_MONTH;
        $this->endDate = $_GET['year'].REPORTING_CYCLE_END_MONTH;
        
        $nodes = array();
        $edges = array();
        switch($nodeType){
            case 'all':
                $nodes = Person::getAllPeopleDuring('all', $this->startDate, $this->endDate);
                break;
            case 'pni':
                $nodes = Person::getAllPeopleDuring(PNI, $this->startDate, $this->endDate);
                break;
            case 'cni':
                $nodes = Person::getAllPeopleDuring(CNI, $this->startDate, $this->endDate);
                break;
            case 'hqp':
                $nodes = Person::getAllPeopleDuring(HQP, $this->startDate, $this->endDate);
                break;
        }
        switch($edgeType){
            case 'all':
                $edges = array_merge($this->getWorksWithEdges($nodes),
                                     $this->getCoProduceEdges($nodes),
                                     $this->getCoSuperviseEdges($nodes));
                break;
            case 'coproduce':
                $edges = $this->getCoProduceEdges($nodes);
                break;
            case 'cosup':
                $edges = $this->getCoSuperviseEdges($nodes);
                break;
            case 'workswith':
                $edges = $this->getWorksWithEdges($nodes);
                break;
        }
        
        $json['nodes'] = array();
        foreach($nodes as $node){
            $json['nodes'][] = $node->getName();
        }
        $json['edges'] = $edges;
        echo json_encode($json);
        exit;
	}
	
	function getCoProduceEdges($nodes){
	    $edges = array();
	    $ids = array();
	    foreach($nodes as $node){
	        $ids[$node->getId()] = true;
	    }
	    foreach($nodes as $person){
	        $products = $person->getPapersAuthored('all', $this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH, true);
	        foreach($products as $product){
	            $authors = $product->getAuthors();
	            foreach($authors as $auth){
	                if(isset($ids[$auth->getId()]) && $person->getId() != $auth->getId()){
	                    $edges[] = array('a' => $person->getName(), 'b' => $auth->getName());
	                }
	            }
	        }
	    }
	    return $edges;
	}
	
	function getCoSuperviseEdges($nodes){
	    if($_GET['nodeType'] == 'hqp'){
	        return array();
	    }
	    $edges = array();
	    $ids = array();
	    foreach($nodes as $node){
	        $ids[$node->getId()] = true;
	    }
	    foreach($nodes as $person){
	        $hqps = $person->getHQPDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH);
	        foreach($hqps as $hqp){
	            $sups = $hqp->getSupervisorsDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH);
	            foreach($sups as $sup){
	                if(isset($ids[$sup->getId()]) && $person->getId() != $sup->getId()){
	                    $edges[] = array('a' => $person->getName(), 'b' => $sup->getName());
	                }
	            }
	        }
	    }
	    return $edges;
	}
	
	function getWorksWithEdges($nodes){
	    $edges = array();
	    $ids = array();
	    foreach($nodes as $node){
	        $ids[$node->getId()] = true;
	    }
	    foreach($nodes as $person){
	        $relations = $person->getRelationsDuring(WORKS_WITH, $this->startDate, $this->endDate);
	        $alreadyDone = array();
	        foreach($relations as $relation){
	            if(isset($ids[$relation->getUser2()->getId()]) && 
	               !isset($alreadyDone[$relation->getUser2()->getId()]) &&
	               $person->getId() != $relation->getUser2()->getId()){
	                $edges[] = array('a' => $relation->getUser1()->getName(), 'b' => $relation->getUser2()->getName());
	                $alreadyDone[$relation->getUser2()->getId()] = true;
	            }
	        }
	    }
	    return $edges;
	}
	
	function isLoginRequired(){
		return false;
	}
}

?>
