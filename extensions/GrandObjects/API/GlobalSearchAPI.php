<?php

class GlobalSearchAPI extends RESTAPI {
    
    function doGET(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $search = $this->getParam('search');
        $group = $this->getParam('group');
        $array = array('search' => $search,
                       'group' => $group);
        $ids = array();
        $origSearch = $search;
        $search = "*".str_replace(" ", "*", $search)."*";
        switch($group){
            case 'people':
                $searchNames = array_filter(explode("*", str_replace(".", "*", strtolower($search))));
                $data = array();
                $people = Person::getAllPeople();
                foreach($people as $person){
                    $realName = $person->getNameForForms();
                    $names = array_merge(explode(".", str_replace(" ", "", strtolower($realName))), 
                                         explode(" ", str_replace(".", "", strtolower($realName))));
                    $found = true;
                    foreach($searchNames as $name){
                        $grepped = preg_grep("/^$name.*/", $names);
                        if(count($grepped) == 0){
                            $found = false;
                            break;
                        }
                    }
                    if($found){
                        $data[] = array('user_id' => $person->getId(),
                                        'user_name' => $person->getName());
                    }
                }
                $results = array();
                $myRelations = $me->getRelations();
                $sups = $me->getSupervisors();
                foreach($data as $row){
                    $person = Person::newFromId($row['user_id']);
                    $continue = false;
                    foreach($person->getRoles() as $role){
                        if($role->getRole() == MANAGER){
                            $continue = true;
                        }
                        if(!$me->isLoggedIn() && $role->getRole() == HQP){
                            $continue = true;
                        }
                    }
                    if($continue) continue;
                    similar_text(strtolower(str_replace(".", " ", $row['user_name'])), strtolower($origSearch), $percent);
                    foreach($person->getProjects() as $project){
                        if($me->isMemberOf($project)){
                            $percent += 15;
                        }
                    }
                    if(count($myRelations) > 0){
                        $relFound = false;
                        foreach($myRelations as $type){
                            if(!$relFound){
                                foreach($type as $relation){
                                    if($relation->getUser2()->getId() == $person->getId()){
                                        $percent += 50;
                                        $relFound = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if(count($sups) > 0){
                        foreach($sups as $sup){
                            if($sup->getId() == $person->getId()){
                                $percent += 50;
                                break;
                            }
                        }
                    }
                    $results[$row['user_id']] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
	            foreach($results as $key => $row){
	                $ids[] = intval($key);
	            }
                break;
            case 'projects':
                $searchNames = array_filter(explode("*", str_replace(".", "*", strtolower($search))));
                $data = array();
                $projects = Project::getAllProjects();
                foreach($projects as $project){
                    $pName = strtolower($project->getName());
                    $pFullName = strtolower($project->getFullName());
                    $names = array_merge(explode(" ", strtolower($pName)),
                                         explode(" ", strtolower($pFullName)));
                    $found = true;
                    foreach($searchNames as $name){
                        $grepped = preg_grep("/^$name.*/", $names);
                        if(count($grepped) == 0){
                            $found = false;
                            break;
                        }
                    }
                    if($found){
                        $data[] = array('project_id' => $project->getId(),
                                        'project_name' => $project->getName());
                    }
                }
                $results = array();
                foreach($data as $row){
                    $project = Person::newFromId($row['project_id']);
                    similar_text(strtolower($row['project_name']), strtolower($origSearch), $percent);
                    if($me->isMemberOf($project)){
                        $percent += 50;
                    }
                    $results[$row['project_id']] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
	            foreach($results as $key => $row){
	                $ids[] = intval($key);
	            }
                break;
            case 'products':
                $searchNames = array_filter(explode("*", str_replace(".", "*", strtolower($search))));
                $data = array();
                $products = Product::getAllPapers('all', 'all', 'both');
                foreach($products as $product){
                    $pTitle = strtolower($product->getTitle());
                    $pCategory = strtolower($product->getCategory());
                    $pType = strtolower($product->getCategory());
                    $names = array_merge(explode(" ", $pTitle),
                                         explode(" ", $pCategory),
                                         explode(" ", $pType));
                    $found = true;
                    foreach($searchNames as $name){
                        $grepped = preg_grep("/^$name.*/", $names);
                        if(count($grepped) == 0){
                            $found = false;
                            break;
                        }
                    }
                    if($found){
                        $data[] = array('product_id' => $product->getId(),
                                        'product_title' => $product->getTitle());
                    }
                }
                $results = array();
                $myProducts = new Collection($me->getPapers('all', false, 'both'));
                $productIds = array_flip($myProducts->pluck('id'));
                foreach($data as $row){
                    $product = Product::newFromId($row['product_id']);
                    similar_text(strtolower($row['product_title']), strtolower($origSearch), $percent);
                    if(isset($productIds[$product->getId()])){
                        $percent += 50;
                    }
                    foreach($product->getProjects() as $project){
                        if($me->isMemberOf($project)){
                            $percent += 15;
                        }
                    }
                    $results[$row['product_id']] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
	            foreach($results as $key => $row){
	                $ids[] = intval($key);
	            }
                break;
            case 'wikipage':
                $results = json_decode(file_get_contents("{$wgServer}{$wgScriptPath}/api.php?action=query&generator=search&gsrwhat=title&gsrsearch=".$search."&format=json"));
                if(isset($results->query)){
                    foreach($results->query->pages as $page){
                        $ids[] = $page->pageid;
                    }
                }
                break;
        }
        $array['results'] = $ids;
        return json_encode($array);
    }
    
    function doPOST(){
        return $this->doGet();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

?>
