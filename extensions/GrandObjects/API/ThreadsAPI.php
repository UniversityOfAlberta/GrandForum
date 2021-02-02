<?php

class ThreadsAPI extends RESTAPI {

    function doGET(){
        $me = Person::newFromWgUser();
        if($this->getParam('search') == ""){
            $threads = array();
            foreach(Thread::getAllThreads() as $thread){
                if($thread->canView()){
                    $threads[] = $thread;
                }
            }
            $threads = new Collection($threads);
        }
        else{
            $threads = array();
            $search = DBFunctions::escape(str_replace('%', '\%', strtolower($this->getParam('search'))));
            $data = DBFunctions::execSQL("SELECT DISTINCT t.id
                                          FROM grand_posts p, grand_threads t
                                          WHERE p.thread_id = t.id
                                          AND (MATCH(p.message) AGAINST ('{$search}') OR 
                                               LOWER(t.title)   LIKE '%{$search}%')");
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
