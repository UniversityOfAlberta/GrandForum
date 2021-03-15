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
                $people = DBFunctions::select(array('mw_user'),
                                              array('user_name', 'user_real_name', 'user_id', 'user_email'),
                                              array('deleted' => '0'));
                $peopleFullText = DBFunctions::execSQL("(SELECT user_id FROM mw_user
                                                         WHERE user_id IN (SELECT user_id FROM grand_person_keywords WHERE keyword LIKE '%".str_replace(" ", "%", $escapedSearch)."%')
                                                         AND deleted = 0)
                                                        UNION
                                                        (SELECT user_id FROM mw_user
                                                         WHERE MATCH(user_public_profile) AGAINST ('+".str_replace(" ", "* +", preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $escapedSearch))."*' IN BOOLEAN MODE)
                                                         AND deleted = 0)
                                                        UNION
                                                        (SELECT user_id FROM mw_user
                                                         WHERE user_id IN (SELECT user_id FROM grand_uofa_news 
                                                                           WHERE MATCH(title) AGAINST ('+".str_replace(" ", "* +", preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $escapedSearch))."*' IN BOOLEAN MODE))
                                                         AND deleted = 0)");

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
                    if($person->isRoleAtLeast(ADMIN)){
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
            case 'projects':
                $data = array();
                $projects = Project::getAllProjectsDuring('0000','9999', true);
                foreach($projects as $project){
                    if(count($project->getSuccs()) > 0){
                        continue;
                    }
                    if($project->getStatus() == "Proposed" && !$me->isRoleAtLeast(STAFF)){
                        continue;
                    }
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
                $productsFullText = DBFunctions::execSQL("SELECT id FROM grand_products
                                                          WHERE MATCH(description) AGAINST ('".str_replace(" ", "* ", DBFunctions::escape(trim($origSearch)))."*')
                                                          AND deleted = 0");
                Product::generateProductTagsCache();
                foreach($products as $product){
                    $pTitle = unaccentChars($product['title']);
                    $pCategory = unaccentChars($product['category']);
                    $pType = unaccentChars($product['type']);
                    $pTags = array();
                    if(isset(Product::$productTagsCache[$product['id']])){
                        foreach(Product::$productTagsCache[$product['id']] as $tag){
                            $pTags[] = $tag;
                        }
                    }
                    $pTags = unaccentChars(implode(" ", $pTags));
                    $names = array_merge(explode(" ", $pTitle),
                                         explode(" ", $pCategory),
                                         explode(" ", $pType),
                                         explode(" ", $pTags));
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
	            
	            $dataCollection = new Collection($productsFullText);
	            $products = Product::getByIds($dataCollection->pluck('id'));
	            foreach($products as $product){
	                if(array_search($product->getId(), $ids) === false){
	                    $ids[] = intval($product->getId());
	                }
	            }
                break;
            case 'bibliographies':
                $data = array();
                $bibs = DBFunctions::select(array('grand_bibliography'),
                                            array('title', 'description', 'id'));
                Product::generateProductTagsCache();
                foreach($bibs as $bibliography){
                    $bTitle = unaccentChars($bibliography['title']);
                    $bDescription = unaccentChars($bibliography['description']);
                    $bib = Bibliography::newFromId($bibliography['id']);
                    $products = $bib->getProducts();
                    $pTags = array();
                    
                    foreach($products as $product){
                        if(isset(Product::$productTagsCache[$product->getId()])){
                            foreach(Product::$productTagsCache[$product->getId()] as $tag){
                                $pTags[] = $tag;
                            }
                        }
                    }

                    $pTags = unaccentChars(implode(" ", $pTags));
                    $names = array_merge(explode(" ", $bTitle),
                                         explode(" ", $bDescription),
                                         explode(" ", $pTags));
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
                        $data[] = array('bib_id' => $bib->getId(),
                                        'bib_title' => $bib->getTitle());
                    }
                }
                $dataCollection = new Collection($data);
                $results = array();
                $bibs = Bibliography::getByIds($dataCollection->pluck('bib_id'));
                
                foreach($bibs as $bibliography){
                    $percent = 0;
                    similar_text(unaccentChars($bibliography->getTitle()), unaccentChars($origSearch), $percent);
                    $results[$bibliography->getId()] = $percent;
                }
                asort($results);
                $results = array_reverse($results, true);
                foreach($results as $key => $row){
                    $ids[] = intval($key);
                }
                break;
            case 'wikipage':
                $url = "{$wgServer}{$wgScriptPath}/api.php?action=query&generator=search&gsrwhat=title&gsrsearch=".$search."&format=json";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // get http header for cookies
                curl_setopt($ch, CURLOPT_HEADER, 0);
                if(!isExtensionEnabled('Shibboleth')){
                    // forward current cookies to curl
                    $cookies = array();
                    foreach($_COOKIE as $key => $value){
                        if ($key != 'Array'){
                            $cookies[] = $key . '=' . $value;
                        }
                    }
                    curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
                }
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
                        if($article != null && $article->getTitle()->userCanRead() && array_search($article->getTitle()->getNSText(), $blacklistedNamespaces) === false){
                            $project = Project::newFromName($article->getTitle()->getNSText());
                            if($project != null && $project->getName() != ""){
                                // Namespace belongs to a project
                                if($project->getType() == 'Administrative' || $me->isMemberOf($project)){
                                    $ids[] = $page->pageid;
                                }
                            }
                            else if(strpos($article->getTitle()->getText(), "MAIL") !== 0){
                                $ids[] = $page->pageid;
                            }
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
