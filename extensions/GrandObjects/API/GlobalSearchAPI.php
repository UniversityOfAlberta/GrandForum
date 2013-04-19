<?php

class GlobalSearchAPI extends RESTAPI {
    
    function doGET(){
        global $wgServer, $wgScriptPath;
        $search = $this->getParam('search');
        $group = $this->getParam('group');
        $array = array('search' => $search,
                       'group' => $group);
        $ids = array();
        $search = $search."*";
        switch($group){
            case 'people':
                $results = json_decode(file_get_contents("http://grand.cs.ualberta.ca:8981/solr/select?&wt=json&debug=results&fl=score,*&defType=dismax&bf=user_exp^20.0&q=".urlencode($search)."&start=0"));
                $docs = $results->response->docs;
                foreach($docs as $doc){
                    $ids[] = $doc->user_id;
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
