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
                $search = mysql_real_escape_string(str_replace("*", "%", str_replace(".", "%", $search)));
                $data = DBFunctions::execSQL("SELECT `user_id`, `user_name`
                                              FROM `mw_user`
                                              WHERE (UPPER(CONVERT(`user_name` USING latin1)) LIKE UPPER('$search')
                                                 OR UPPER(CONVERT(`user_real_name` USING latin1)) LIKE UPPER('$search'))
                                                 AND `deleted` != '1'");
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
                        foreach($myRelations as $type){
                            foreach($type as $relation){
                                if($relation->getUser2()->getId() == $person->getId()){
                                    $percent += 50;
                                }
                            }
                        }
                    }
                    if(count($sups) > 0){
                        foreach($sups as $sup){
                            if($sup->getId() == $person->getId()){
                                $percent += 50;
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
