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
        $searchNames = array_filter(explode("*", str_replace(".", "*", unaccentChars($search))));
        switch($group){
            case 'people':
                $data = array();
                if(!$me->isLoggedIn()){
                    $results = $data;
                    break;
                }
                $people = DBFunctions::select(array('mw_user'),
                                              array('user_name', 'user_real_name', 'user_id', 'user_email'),
                                              array('deleted' => '0'));
                foreach($people as $pRow){
                    $person = new Person(array());
                    $person->name = $pRow['user_name'];
                    $person->realname = $pRow['user_real_name'];
                    if($me->isLoggedIn()){
                        // Only search by email if the person is logged in
                        $person->email = $pRow['user_email'];
                    }
                    $realName = $person->getNameForForms();
                    $names = array_merge(explode(".", str_replace(" ", "", unaccentChars($realName))), 
                                         explode(" ", str_replace(".", "", unaccentChars($realName))));
                    $names[] = unaccentChars($person->getEmail());
                    $found = true;
                    foreach($searchNames as $name){
                        $name = preg_quote($name);
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
                    if($person->getName() == "Admin"){
                        // Don't include Admin
                        $continue = true; 
                    }
                    if(!$me->isLoggedIn() || (!$person->isRole(MANAGER) && !$person->isRole(Expert) && !$me->isRoleAtLeast(MANAGER))){
                        $continue = true;
                    }
                    if($continue) continue;
                    similar_text(unaccentChars(str_replace(".", " ", $person->getName())), unaccentChars($origSearch), $percent);
                    if(!$person->isActive()){
                        $percent -= 10;
                    }
                    foreach($person->getProjects() as $project){
                        if($me->isMemberOf($project)){
                            $percent += 15;
                        }
                    }
                    if(!empty($myRelations)){
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
                    if(!empty($sups)){
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
                $projects = Project::getAllProjectsDuring('0000','9999', true);
                foreach($projects as $project){
                    $pName = unaccentChars(str_replace(".", " ", $project->getName()));
                    $pFullName = unaccentChars(str_replace(".", " ", $project->getFullName()));
                    $names = array_merge(explode(" ", unaccentChars($pName)),
                                         explode(" ", unaccentChars($pFullName)));
                    $found = true;
                    foreach($searchNames as $name){
                        $name = preg_quote($name);
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
                    $project = Project::newFromId($row['project_id']);
                    similar_text(unaccentChars($row['project_name']), unaccentChars($origSearch), $percent);
                    if($me->isMemberOf($project)){
                        $percent += 50;
                    }
                    if($project->isDeleted()){
                        $percent -= 50;
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
                    $pTitle = unaccentChars($product['title']);
                    $pCategory = unaccentChars($product['category']);
                    $pType = unaccentChars($product['type']);
                    $names = array_merge(explode(" ", $pTitle),
                                         explode(" ", $pCategory),
                                         explode(" ", $pType));
                    $found = true;
                    foreach($searchNames as $name){
                        $name = preg_quote($name);
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
                $flippedProductIds = @array_flip($productIds);
                
                $products = Product::getByIds($dataCollection->pluck('product_id'));
                foreach($products as $product){
                    $percent = 0;
                    similar_text(unaccentChars($product->getTitle()), unaccentChars($origSearch), $percent);
                    if(isset($flippedProductIds[$product->getId()])){
                        $percent += 50;
                    }
                    $results[$product->getId()] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
                foreach($results as $key => $row){
                    $ids[] = intval($key);
                }
                break;
        case 'stories':
                $data = array();
                $stories = Story::getAllUserStories();
                foreach($stories as $story){
                    $pName = unaccentChars(str_replace(".", " ", $story->getTitle()));
                    $names = array_merge(explode(" ", unaccentChars($pName)));
                    $found = true;
                    foreach($searchNames as $name){
                        $name = preg_quote($name);
                        $grepped = preg_grep("/^$name.*/", $names);
                        if(count($grepped) == 0){
                            $found = false;
                            break;
                        }
                    }
                    if($found){
                        $data[] = array('story_id' => $story->getId(),
                                        'story_title' => $story->getTitle());
                    }
                }
                $results = array();
                foreach($data as $row){
                    $story = Story::newFromId($row['story_id']);
                    if($story->canView()){
                        similar_text(unaccentChars($row['story_title']), unaccentChars($origSearch), $percent);
                        if($story->isOwnedBy($me)){
                            $percent += 50;
                        }
                        $results[$row['story_id']] = $percent;
                    }
                }
                asort($results);
                $results = array_reverse($results, true);
                foreach($results as $key => $row){
                    $ids[] = intval($key);
                }
                break;
        case 'threads':
                $data = array();
                $threads = Thread::getAllThreads();
                foreach($threads as $thread){
                    $pName = unaccentChars(str_replace(".", " ", $thread->getTitle()));
                    $names = array_merge(explode(" ", unaccentChars($pName)));
                    $found = true;
                    foreach($searchNames as $name){
                        $name = preg_quote($name);
                        $grepped = preg_grep("/^$name.*/", $names);
                        if(count($grepped) == 0){
                            $found = false;
                            break;
                        }
                    }
                    if($found){
                        $data[] = array('thread_id' => $thread->getId(),
                                        'thread_title' => $thread->getTitle());
                    }
                }
                $results = array();
                foreach($data as $row){
                    $thread = Thread::newFromId($row['thread_id']);
                    if($thread->canView()){
                        similar_text(unaccentChars($row['thread_title']), unaccentChars($origSearch), $percent);
                        if($thread->getThreadOwner() === $me->getId()){
                            $percent += 50;
                        }
                        $results[$row['thread_id']] = $percent;
                    }
                }
                asort($results);
                $results = array_reverse($results, true);
                foreach($results as $key => $row){
                    $ids[] = intval($key);
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
