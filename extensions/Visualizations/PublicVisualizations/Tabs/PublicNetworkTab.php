<?php

$wgHooks['UnknownAction'][] = 'PublicNetworkTab::getPublicNetworkData';

class PublicNetworkTab extends AbstractTab {
	
	function PublicNetworkTab(){
        parent::AbstractTab("Network");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath, $config;
        $graph = new ForceDirectedGraph("{$wgServer}{$wgScriptPath}/index.php?action=getPublicNetworkData");
        $graph->height = 600;
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
	
	static function addEdge(&$edges, $from, $to){
	    if(isset($edges[$from.$to])){
	        $edges[$from.$to]['width'] += 1;
	    }
	    else if(isset($edges[$to.$from])){
	        $edges[$to.$from]['width'] += 1;
	    }
	    else{
	        $edges[$from.$to] = array("from" => $from,
	                                  "to" => $to,
	                                  "width" => 1);
	    }
	}
	
	static function getPublicNetworkData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    $me = Person::newFromWgUser();
	    if($action == "getPublicNetworkData"){
	        session_write_close();
	        
	        $people = Person::getAllPeople();
	        $projects = Project::getAllProjects();
	        $themes = Theme::getAllThemes();
	        //$products = Product::getAllPapers();
	        
	        $nodes = array();
	        $edges = array();
	        
	        foreach($themes as $theme){
	            $nodes[] = array("id"    => "theme{$theme->getId()}",
	                             "label" => "{$theme->getAcronym()}",
	                             "title" => "{$theme->getName()}",
	                             "value" => 30,
	                             "group" => 1,
	                             "color" => $theme->getColor());
	        }
	        
	        foreach($people as $person){
	            $nodes[] = array("id"    => "person{$person->getId()}",
	                             "label" => "{$person->getNameForForms()}",
	                             "title" => "{$person->getNameForForms()} ({$person->getRoleString()})",
	                             "value" => 10,
	                             "group" => 3);
	            foreach($person->getRelations() as $relationType){
	                foreach($relationType as $relation){
	                    self::addEdge($edges, "person{$relation->getUser1()->getId()}", "person{$relation->getUser2()->getId()}");
	                }
	            }
	        }
	        
	        foreach($projects as $project){
	            $nodes[] = array("id"    => "project{$project->getId()}",
	                             "label" => "{$project->getName()}",
	                             "title" => "{$project->getFullName()}",
	                             "value" => 20,
	                             "group" => 2);
	            foreach($project->getChallenges() as $challenge){
	                self::addEdge($edges, "project{$project->getId()}", "theme{$challenge->getId()}");
	            }
	            foreach($project->getAllPeople() as $person){
	                self::addEdge($edges, "person{$person->getId()}", "project{$project->getId()}");
	            }
	        }
	        
	        /*
	        foreach($products as $product){
	            foreach($product->getAuthors() as $person1){
	                if($person1->getId() != 0){
	                    foreach($product->getAuthors() as $person2){
	                        if($person2->getId() != 0 && $person1 != $person2){
	                            self::addEdge($edges, "person{$person1->getId()}", "person{$person2->getId()}");
	                        }
	                    }
	                }
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
