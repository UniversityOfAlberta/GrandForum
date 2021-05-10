<?php

class RSSArticle extends BackboneModel {

    var $id;
    var $feed;
    var $rssId;
    var $url;
    var $title;
    var $date;
    var $description;
    var $people = array();
    var $projects = array();
    var $keywords = array();

    static function getAllArticles(){
        $data = DBFunctions::select(array('grand_rss_articles'),
                                    array('*'),
                                    array('deleted' => NEQ(1)));
        $feeds = array();
        foreach($data as $row){
            $feeds[] = new RSSArticle(array($row));
        }
        return $feeds;
    }
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_rss_articles'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new RSSArticle($data);
    }
    
    static function newFromRSSId($id){
        $data = DBFunctions::select(array('grand_rss_articles'),
                                    array('*'),
                                    array('rss_id' => EQ($id)));
        return new RSSArticle($data);
    }

    function RSSArticle($data=null){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->feed = $data[0]['feed'];
            $this->rssId = $data[0]['rss_id'];
            $this->url = $data[0]['url'];
            $this->title = $data[0]['title'];
            $this->date = $data[0]['date'];
            $this->description = $data[0]['description'];
            $this->people = json_decode($data[0]['people']);
            $this->projects = json_decode($data[0]['projects']);
            $this->keywords = json_decode($data[0]['keywords']);
        }
    }
    
    function getDate(){
        return substr($this->date, 0, 10);
    }
    
    function getPeople(){
        $people = array();
        foreach($this->people as $id){
            $person = Person::newFromId($id);
            if($person != null && $person->getId() > 0){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    function getProjects(){
        $projects = array();
        foreach($this->projects as $id){
            $project = Project::newFromId($id);
            if($project != null && $project->getId() > 0){
                $projects[] = $project;
            }
        }
        return $projects;
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'feed' => $this->feed,
                     'rssId' => $this->rssId,
                     'url' => $this->url,
                     'title' => $this->title,
                     'date' => $this->date,
                     'description' => $this->description,
                     'people' => $this->people,
                     'projects' => $this->projects,
                     'keywords' => $this->keywords);
    }
    
    function create(){
        $status = DBFunctions::insert('grand_rss_articles',
                                      array('feed' => $this->feed,
                                            'rss_id' => $this->rssId,
                                            'url' => "{$this->url}",
                                            'title' => "{$this->title}",
                                            'date' => "{$this->date}",
                                            'description' => "{$this->description}",
                                            'people' => json_encode($this->people),
                                            'projects' => json_encode($this->projects),
                                            'keywords' => json_encode($this->keywords)));
        if($status){
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
        }
        return $status;
    }
    
    function update(){
        $status = DBFunctions::update('grand_rss_articles',
                                      array('feed' => $this->feed,
                                            'rss_id' => $this->rssId,
                                            'url' => $this->url,
                                            'title' => $this->title,
                                            'date' => $this->date,
                                            'description' => $this->description,
                                            'people' => json_encode($this->people),
                                            'projects' => json_encode($this->projects),
                                            'keywords' => json_encode($this->keywords)),
                                      array('id' => EQ($this->id)));
        if($status){
            DBFunctions::commit();
        }
        return $status;
    }
    
    function delete(){
        $status = DBFunctions::update('grand_rss_articles',
                                      array('deleted' => 1),
                                      array('id' => EQ($this->id)));
        return $status;
    }
    
    function exists(){
        return ($this->id > 0);
    }
    
    function getCacheId(){
        return "";
    }

}

?>
