<?php

class GlobalSearchAPI extends RESTAPI {
    
    function doGET(){
        global $wgServer, $wgScriptPath, $wgUser;
        $me = Person::newFromWgUser();
        $search = $this->getParam('search');
        $group = $this->getParam('group');
        $array = array('search' => $search,
                       'group' => $group);
        $ids = array();
        $origSearch = $search;
        $search = "*".str_replace(" ", "*", $search)."*";
        $searchNames = array_filter(explode("*", str_replace(".", "*", strtolower($search))));
        switch($group){
            case 'people':
                $data = array();
                $people = DBFunctions::select(array('mw_user'),
                                                array('user_name', 'user_real_name', 'user_id'),
                                                array('deleted' => '0'));
                foreach($people as $pRow){
                    $person = new Person(array());
                    $person->name = $pRow['user_name'];
                    $person->realname = $pRow['user_real_name'];
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
                        $data[] = array('user_id' => $pRow['user_id'],
                                        'user_name' => $person->getName());
                    }
                }
                $results = array();
                $myRelations = $me->getRelations();
                $sups = $me->getSupervisors();
                $dataCollection = new Collection($data);
                $people = Person::getByIds($dataCollection->pluck('user_id'));
                foreach($people as $person){
                    $continue = false;
                    foreach($person->getRoles() as $role){
                        if($role->getRole() == MANAGER && !$me->isRole(MANAGER)){
                            $continue = true;
                        }
                        if(!$me->isLoggedIn() && $role->getRole() == HQP){
                            $continue = true;
                        }
                    }
                    if($continue) continue;
                    similar_text(strtolower(str_replace(".", " ", $person->getName())), strtolower($origSearch), $percent);
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
                    $results[$person->getId()] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
	            foreach($results as $key => $row){
	                $ids[] = intval($key);
	            }
                break;
            case 'projects':
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
                $data = array();
                $products = DBFunctions::select(array('grand_products'),
                                                array('title', 'category', 'type', 'id'),
                                                array('deleted' => '0'));
                foreach($products as $product){
                    $pTitle = strtolower($product['title']);
                    $pCategory = strtolower($product['category']);
                    $pType = strtolower($product['type']);
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
                        $data[] = array('product_id' => $product['id'],
                                        'product_title' => $product['title']);
                    }
                }
                $dataCollection = new Collection($data);
                $results = array();
                $myProducts = new Collection($me->getPapers('all', false, 'both'));
                $productIds = $myProducts->pluck('id');
                $flippedProductIds = array_flip($myProducts->pluck('id'));
                $products = Product::getByIds($dataCollection->pluck('product_id'));
                foreach($products as $product){
                    similar_text(strtolower($product->getTitle()), strtolower($origSearch), $percent);
                    if(isset($flippedProductIds[$product->getId()])){
                        $percent += 50;
                    }
                    foreach($product->getProjects() as $project){
                        if($me->isMemberOf($project)){
                            $percent += 15;
                        }
                    }
                    $results[$product->getId()] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
	            foreach($results as $key => $row){
	                $ids[] = intval($key);
	            }
                break;
            case 'wikipage':
                $results = json_decode(file_get_contents("{$wgServer}{$wgScriptPath}/api.php?action=query&generator=search&gsrwhat=title&gsrsearch=".$search."&format=json"));
                $blacklistedNamespaces = array('Publication',
                                               'Artifact',
                                               'Presentation',
                                               'Activity',
                                               'Press',
                                               'Award',
                                               'PNI',
                                               'HQP',
                                               'CNI');
                if(isset($results->query)){
                    foreach($results->query->pages as $page){
                        $article = Article::newFromId($page->pageid);
                        if($article->getTitle()->userCanRead() && array_search($article->getTitle()->getNSText(), $blacklistedNamespaces) === false){
                            $ids[] = $page->pageid;
                        }
                    }
                }
                break;
            case 'specialpage':
                global $wgSpecialPages;
                foreach($wgSpecialPages as $specialPage){
                    $special = new $specialPage();
                    if($special->userCanExecute($wgUser)){
                        $ids[] = '';
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
