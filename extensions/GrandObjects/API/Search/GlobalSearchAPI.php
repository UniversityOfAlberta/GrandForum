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
        $escapedSearch = str_replace("%", "\%", DBFunctions::escape(trim($origSearch)));
        $search = "*".str_replace(" ", "*", $search)."*";
        $searchNames = array_filter(explode("*", str_replace(".", "*", unaccentChars($search))));
        switch($group){
            case 'people':
                $data = array();
                $fullTextSearch = DBFunctions::escape(implode("* +", $searchNames));
                $likeSearch = DBFunctions::escape(implode("%", $searchNames));
                $reversedLikeSearch = DBFunctions::escape(implode("%", array_reverse($searchNames)));
                $people = DBFunctions::execSQL("SELECT DISTINCT user_id
                                                FROM `grand_names_cache`
                                                WHERE name LIKE '%$likeSearch%'
                                                OR name LIKE '%$reversedLikeSearch%' 
                                                LIMIT 100");
                /*$people = DBFunctions::execSQL("SELECT DISTINCT user_id
                                                FROM `grand_names_cache`
                                                WHERE MATCH(name) AGAINST ('+$fullTextSearch*' IN BOOLEAN MODE) 
                                                LIMIT 100");*/
                $peopleFullText = DBFunctions::execSQL("(SELECT user_id FROM mw_user
                                                         WHERE user_id IN (SELECT user_id FROM grand_person_keywords WHERE keyword LIKE '%".str_replace(" ", "%", $escapedSearch)."%')
                                                         AND deleted = 0)");
                                                         
                foreach($people as $person){
                    $data[$person['user_id']] = $person['user_id'];
                }
                if(count($searchNames) == 1 && $me->isLoggedIn()){
                    // Only search email if a single search word was used
                    foreach($searchNames as $name){
                        $name = DBFunctions::escape($name);
                        $people = DBFunctions::execSQL("SELECT DISTINCT user_id
                                                        FROM `mw_user`
                                                        WHERE user_email LIKE '$name%'
                                                        AND deleted != 1
                                                        LIMIT 100");
                        foreach($people as $person){
                            $data[$person['user_id']] = $person['user_id'];
                        }
                    }
                }
                
                $results = array();
                $myRelations = $me->getRelations();
                $sups = $me->getSupervisors();
                $people = Person::getByIds($data);
                foreach($people as $person){
                    $continue = false;
                    if($person->getName() == "Admin"){
                        // Don't include Admin
                        $continue = true; 
                    }
                    if(!$me->isLoggedIn() && !$person->isRoleAtLeast(NI)){
                        $continue = true;
                    }
                    if($continue) continue;
                    similar_text(unaccentChars(str_replace(".", " ", $person->getName())), unaccentChars($origSearch), $percent);
                    if(!$person->isActive()){
                        $percent -= 10;
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
	            foreach($peopleFullText as $person){
	                if(array_search($person['user_id'], $ids) === false){
	                    $p = Person::newFromId($person['user_id']);
	                    if($p->isRoleAtLeast(ADMIN)){
                            // Don't include Admin
                            continue;
                        }
                        if(!$me->isLoggedIn() && !$p->isRoleAtLeast(NI) && !$config->getValue('hqpIsPublic')){
                            continue;
                        }
	                    $ids[] = intval($person['user_id']);
	                }
	            }
                break;
            case 'products':
                $data = array();
                $rows = DBFunctions::select(array('grand_products'),
                                                 array('title', 'id', 'category'),
                                                 array('deleted' => '0',
                                                       'access_id' => '0'));
                foreach($rows as $product){
                    if($product['category'] == "Publication" ||
                       $product['category'] == "Presentation"){
                        $pTitle = unaccentChars($product['title']);
                        $names = array_merge(explode(" ", $pTitle));
                        $found = true;
                        foreach($searchNames as $name){
                            $grepped = preg_grep("/^$name.*/", $names);
                            if(count($grepped) == 0){
                                $found = false;
                                break;
                            }
                        }
                        if($found){
                            $data[$product['id']] = $pTitle;
                        }
                    }
                }
                $results = array();
                $papers = $me->getPapers('all', false, 'both');
                $myProducts = new Collection($papers);
                $productIds = $myProducts->pluck('id');
                $flippedProductIds = @array_flip($productIds);

                $origSearch2 = unaccentChars($origSearch);
                foreach($data as $id => $title){
                    $percent = 0;
                    similar_text($title, $origSearch2, $percent);
                    if(isset($flippedProductIds[$id])){
                        $percent += 50;
                    }
                    $results[$id] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
	            foreach($results as $key => $row){
	                if(count($ids) < 100){
	                    $product = Product::newFromId($key);
	                    if($product->canView()){
	                        $ids[] = intval($key);
	                    }
	                }
	                else{
	                    break;
	                }
	            }
                break;
            case 'wikipage':
                $url = "{$wgServer}{$wgScriptPath}/api.php?action=query&generator=search&gsrwhat=title&gsrsearch=".$search."&format=json";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // get http header for cookies
                curl_setopt($ch, CURLOPT_HEADER, 0);

                // forward current cookies to curl
                $cookies = array();
                foreach($_COOKIE as $key => $value){
                    if ($key != 'Array'){
                        $cookies[] = $key . '=' . $value;
                    }
                }
                curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
                $response = curl_exec($ch);
                curl_close($ch);
                $results = json_decode($response);
                $blacklistedNamespaces = array('Publication',
                                               'Artifact',
                                               'Presentation',
                                               'Activity',
                                               'Press',
                                               'Award',
                                               'NI',
                                               'HQP',
                                               'Mail');
                if(isset($results->query)){
                    foreach($results->query->pages as $page){
                        $article = Article::newFromId($page->pageid);
                        if($article->getTitle()->userCanRead() && array_search($article->getTitle()->getNSText(), $blacklistedNamespaces) === false){
                            if(strpos($article->getTitle()->getText(), "MAIL") !== 0){
                                $ids[] = $page->pageid;
                            }
                        }
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
