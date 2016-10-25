<?php

class ThreadsAPI extends RESTAPI {

    function doGET(){
        $me = Person::newFromWgUser();
        $board_id = $this->getParam('board');
        if($this->getParam('search') == ""){
            $threads = new Collection(Thread::getAllThreads($board_id));
        }
        else{
            $threads = array();
            $search = DBFunctions::escape(str_replace('%', '\%', strtolower($this->getParam('search'))));
            $data = DBFunctions::execSQL("SELECT DISTINCT t.id
                                          FROM grand_posts p, grand_threads t, mw_user u
                                          WHERE p.thread_id = t.id
                                          AND u.user_id = t.user_id
                                          AND (MATCH(p.search) AGAINST ('{$search}') OR 
                                               LOWER(t.title) LIKE '%{$search}%' OR
                                               UPPER(CONVERT(u.user_real_name USING latin1)) LIKE '%{$search}%')
                                          ");
            foreach($data as $row){
                $thread = Thread::newFromId($row['id']);
                if($thread->canView()){
                    $threads[] = $thread;
                }
            }
            $threads = new Collection($threads);
        }
        return $threads->toJSON();
    }

    function doPOST(){
        return false;
    }

    function doPUT(){
        return false;
    }

    function doDELETE(){
        return false;
    }

}

?>
