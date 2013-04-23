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
                $searchNames = array_filter(explode("*", str_replace(".", "*", $search)));
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
                    $percent = 10;
                    similar_text(str_replace(".", " ", $row['user_name']), $origSearch, $percent);
                    $percent = max(10, $percent);
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
                /*$results = json_decode(file_get_contents("http://grand.cs.ualberta.ca:8981/solr/select?&wt=json&debug=results&fl=score,*&defType=dismax&bf=user_exp^20.0&q=".urlencode($search)."&start=0"));
                $docs = $results->response->docs;
                foreach($docs as $doc){
                    $ids[] = $doc->user_id;
                }*/
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
