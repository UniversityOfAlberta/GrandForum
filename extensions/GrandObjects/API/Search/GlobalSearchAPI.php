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
                $person = Person::newFromGSMSId($origSearch);
                if (($person != null) && ($person->getId() != 0)) {
                    $array['results'][] = $person->getId();
                    return json_encode($array);
                }

                $data = array();
                $people = DBFunctions::select(array('mw_user'),
                                              array('user_name', 'user_real_name', 'user_id', 'user_email'),
                                              array('deleted' => '0'));
                foreach($people as $pRow){
                    $check = Person::newFromName($pRow['user_name']);
                    if($check->getId() == 0){
                        continue;
                    }
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
                break;
            case 'wikipage':
                $url = "{$wgServer}{$wgScriptPath}/api.php?action=query&generator=search&gsrwhat=title&gsrsearch=".$search."&format=json";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
                        if($article->getTitle()->userCanRead() && array_search($article->getTitle()->getNSText(), $blacklistedNamespaces) === false){
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
            case 'pdf':
                if(!$me->isRoleAtLeast(STAFF)){
                    break;
                }
                $words = array_filter(explode("*", $search));
                $leftOvers = array();
                $year = "";
                $projects = array();
                $people = array();
                foreach($words as $word){
                    if(is_numeric($word)){
                        // Must be a year
                        $year = $word;
                    }
                    else{
                        $this->params['search'] = $word;
                        $this->params['group'] = "people";
                        $json = json_decode($this->doGET());
                        $people = array_merge($people, $json->results);
                        
                        $this->params['search'] = $word;
                        $this->params['group'] = "projects";
                        $json = json_decode($this->doGET());
                        $projects = array_merge($projects, $json->results);
                    }
                }
                ini_set('memory_limit', '192M');
                $pdfs = PDF::getAllPDFs();
                $latestRows = array();
                foreach($pdfs as $pdf){
                    if(!(count($people) == 0 || count($projects) == 0) &&
                       !(count($people) > 0 && in_array($pdf->getPerson()->getId(), $people)) &&
                       !(count($projects) > 0 && in_array($pdf->getProjectId(), $projects))){
                        continue;
                    }
                    if($year != "" && $pdf->getYear() != $year){
                        continue;
                    }
                    $userId = $pdf->getPerson()->getId();
                    $pdfYear = $pdf->getYear();
                    $type = $pdf->getType();
                    $projectId = $pdf->getProjectId();
                    
                    // Filter out old rows
                    if($type == RPTP_LEADER ||
                       $type == RPTP_LEADER_COMMENTS){
                       $userId = "";
                    }
                    $id = "{$userId}_{$pdfYear}_{$type}_{$projectId}";
                    $tok = $pdf->getId();
                    if(!isset($latestRows[$id])){
                        $latestRows[$id] = $pdf;
                    }
                    else{
                        $cmpStr1 = $latestRows[$id]->getTimestamp()."_".$latestRows[$id]->getReportId();
                        $cmpStr2 = $pdf->getTimestamp()."_".$pdf->getReportId();
                        if($cmpStr1 <= $cmpStr2){
                            $latestRows[$id] = $pdf;
                        }
                    }
                }
                $results = array();
                foreach($latestRows as $pdf){
                    if(!$pdf->canUserRead()){
                        continue;
                    }
                    $project = $pdf->getProject();
                    $keywords = "";
                    $skip = false;
                    $extraKeywords = $year." ".$pdf->getPerson()->getReversedName();
                    if($project != null){
                        $extraKeywords .= " ".$project->getName();
                    }
                    switch($pdf->getType()){
                        case RPTP_NORMAL:
                            $keywords = "ni individual report pdf";
                            break;
                        case RPTP_HQP:
                            $keywords = "hqp individual report pdf";
                            break;
                        case RPTP_CHAMP:
                            $keywords = "champ champion report project pdf";
                            break;
                        case RPTP_NI_COMMENTS:
                            $keywords = "ni individual report milestone comments pdf";
                            break;
                        case RPTP_HQP_COMMENTS:
                            $keywords = "hqp individual report milestone comments pdf";
                            break;
                        case RPTP_LEADER:
                            $keywords = "project leader report pdf";
                            break;
                        case RPTP_LEADER_COMMENTS:
                            $keywords = "project leader report comments pdf";
                            break;
                        case RPTP_MTG:
                            $keywords = "mind the gap mtg pdf";
                            break;
                    }
                    if(!$skip){
                        $results[$pdf->getId()] = 0;
                        foreach($words as $word){
                            $text = unaccentChars($extraKeywords." ".$keywords);
                            if(strstr($text, $word) !== false){
                                $results[$pdf->getId()] += (1.0/max(1, count(explode(" ", $text))));
                            }
                            else{
                                unset($results[$pdf->getId()]);
                                break;
                            }
                        }
                    }
                }
                asort($results);
                $results = array_reverse($results, true);
	            $ids = array_keys($results);
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
