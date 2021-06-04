<?php

$wgHooks['UnknownAction'][] = 'PublicNetworkTab::getPublicNetworkData';

class PublicNetworkTab extends AbstractTab {
	
	function PublicNetworkTab(){
        parent::AbstractTab("Network");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
        $graph = new ForceDirectedGraph("{$wgServer}{$wgScriptPath}/index.php?action=getPublicNetworkData");
        $graph->height = 700;
        $graph->width = "100%";
        $this->html .= "{$graph->show()}";
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'network'){
                    onLoad{$graph->index}();
                }
            });
            </script>
            <p></p>";
	}
	
	static function addEdge(&$edges, $from, $to, $color="", $hidden=false){
	    if($color != ""){
            $color = array("color" => $color,
                           "opacity" => 1);
        }
        else{
            $color = array("opacity" => 1);
        }
	    if(isset($edges[$from.$to])){
	        $edge = $edges[$from.$to];
	        $edge['width'] += 1;
	        $edge['width'] = min($edge, 5);
	        $edge['color'] = $color;
	        unset($edges[$from.$to]);   // Replace old one
	        $edges[$from.$to] = $edge;
	    }
	    else if(isset($edges[$to.$from])){
	        $edge = $edges[$to.$from];
	        $edge['width'] += 1;
	        $edge['width'] = min($edge, 5);
	        $edge['color'] = $color;
	        unset($edges[$to.$from]);   // Replace old one
	        $edges[$to.$from] = $edge;
	    }
	    else{
	        $edges[$from.$to] = array("from" => $from,
	                                  "to" => $to,
	                                  "width" => 1,
	                                  "color" => $color,
	                                  "hidden" => $hidden);
	    }
	}
	
	static function getPublicNetworkData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    if($action == "getPublicNetworkData"){
	        session_write_close();
	        
	        $people = Person::getAllPeople(NI);
	        $projects = Project::getAllProjects();
	        
	        $nodes = array();
	        $edges = array();

	        $products = Product::getAllPapers();
	        foreach($products as $product){
	            foreach($product->getAuthors() as $person1){
	                if($person1->getId() != 0){
	                    foreach($product->getAuthors() as $person2){
	                        if($person2->getId() != 0 && $person1 != $person2 && isset($people[$person1->getName()]) && isset($people[$person2->getName()])){
	                            self::addEdge($edges, "person{$person1->getId()}", "person{$person2->getId()}");
	                        }
	                    }
	                }
	            }
	        }
	        
	        foreach($people as $person){
	            $nodes[] = array("id"    => "person{$person->getId()}",
	                             "label" => "{$person->getNameForForms()}",
	                             "title" => "{$person->getNameForForms()} ({$person->getRoleString()})",
	                             "value" => 10,
	                             "group" => "people");
	            foreach($person->getRelations() as $relationType){
	                foreach($relationType as $relation){
	                    if(isset($people[$relation->getUser1()->getName()]) && isset($people[$relation->getUser2()->getName()])){
	                        self::addEdge($edges, "person{$relation->getUser1()->getId()}", "person{$relation->getUser2()->getId()}");
	                    }
	                }
	            }
	        }
	        
	        
	        foreach($projects as $project){
	            $challenges = $project->getChallenges();
	            $challenge = @$challenges[0];
	            foreach($project->getAllPeople() as $person1){
	                foreach($project->getAllPeople() as $person2){
	                    if($person1 != $person2 && isset($people[$person1->getName()]) && isset($people[$person2->getName()])){
	                        self::addEdge($edges, "person{$person1->getId()}", "person{$person2->getId()}", $challenge->getColor());
	                    }
	                }
	            }
	        }
	        /*
	        foreach($projects as $project){
	            $challenges = $project->getChallenges();
	            $challenge = @$challenges[0];
	            $nodes[] = array("id"    => "project{$project->getId()}",
	                             "label" => "{$project->getName()}",
	                             "title" => "{$project->getFullName()}",
	                             "value" => 20,
	                             "group" => "projects",
	                             "mass" => 0,
	                             "color" => $challenge->getColor());
	            foreach($project->getAllPeople() as $person){
	                self::addEdge($edges, "project{$project->getId()}", "person{$person->getId()}", $challenge->getColor());
	            }
	        }*/
	        
	        $data = array('nodes' => array_values($nodes),
	                      'edges' => array_values($edges));
	        header("Content-Type: application/json");
	        echo json_encode($data);
	        exit;
        }
        return true;
	}
}
?>
