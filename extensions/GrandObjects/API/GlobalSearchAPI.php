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
                $people = DBFunctions::select(array('mw_user'),
                                              array('user_name', 'user_real_name', 'user_id'),
                                              array('deleted' => '0'));
                foreach($people as $pRow){
                    $person = new Person(array());
                    $person->name = $pRow['user_name'];
                    $person->realname = $pRow['user_real_name'];
                    $realName = $person->getNameForForms();
                    $names = array_merge(explode(".", str_replace(" ", "", unaccentChars($realName))), 
                                         explode(" ", str_replace(".", "", unaccentChars($realName))));
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
                    similar_text(unaccentChars(str_replace(".", " ", $person->getName())), unaccentChars($origSearch), $percent);
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
                $projects = Project::getAllProjectsDuring('0000','9999', true);
                foreach($projects as $project){
                    $pName = unaccentChars($project->getName());
                    $pFullName = unaccentChars($project->getFullName());
                    $names = array_merge(explode(" ", unaccentChars($pName)),
                                         explode(" ", unaccentChars($pFullName)));
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
                $results = json_decode(file_get_contents("{$wgServer}{$wgScriptPath}/api.php?action=query&generator=search&gsrwhat=title&gsrsearch=".$search."&format=json"));
                $blacklistedNamespaces = array('Publication',
                                               'Artifact',
                                               'Presentation',
                                               'Activity',
                                               'Press',
                                               'Award',
                                               'PNI',
                                               'HQP',
                                               'CNI',
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
                            $keywords = "ni pni cni individual report pdf";
                            break;
                        case RPTP_HQP:
                            $keywords = "hqp individual report pdf";
                            break;
                        case RPTP_CHAMP:
                            $keywords = "champ champion report project pdf";
                            break;
                        case RPTP_NI_COMMENTS:
                            $keywords = "ni pni cni individual report milestone comments pdf";
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
                        case RPTP_LOI_REVIEW:
                            $keywords = "loi project review report pdf";
                            break;
                        case RPTP_LOI_EVAL_REVIEW:
                            $keywords = "loi project evaluator review report pdf";
                            break;
                        case RPTP_LOI_EVAL_FEEDBACK:
                            $keywords = "loi project evaluator feedback report pdf";
                            break;
                        case RPTP_LOI_REV_REVIEW:
                            $keywords = "loi project reviewer review report pdf";
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
