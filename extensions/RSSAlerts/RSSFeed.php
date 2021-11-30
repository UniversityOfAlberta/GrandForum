<?php

class RSSFeed extends BackboneModel {

    var $id;
    var $url;
    var $filter;

    static function getAllFeeds(){
        $data = DBFunctions::select(array('grand_rss_feeds'),
                                    array('*'));
        $feeds = array();
        foreach($data as $row){
            $feeds[] = new RSSFeed(array($row));
        }
        return $feeds;
    }
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_rss_feeds'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new RSSFeed($data);
    }

    function __construct($data=null){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->url = $data[0]['url'];
            $this->filter = $data[0]['filter'];
        }
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'url' => $this->url,
                     'filter' => $this->filter);
    }
    
    function create(){
        DBFunctions::insert('grand_rss_feeds',
                            array('url' => $this->url,
                                  'filter' => $this->filter));
        $this->id = DBFunctions::insertId();
        DBFunctions::commit();
    }
    
    function update(){
        DBFunctions::update('grand_rss_feeds',
                            array('url' => $this->url,
                                  'filter' => $this->filter),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
    }
    
    function delete(){
        DBFunctions::delete('grand_rss_feeds',
                            array('id' => EQ($this->id)));
        DBFunctions::delete('grand_rss_articles',
                            array('feed' => EQ($this->id)));
        DBFunctions::commit();
    }
    
    function exists(){
        return ($this->id > 0);
    }
    
    function getCacheId(){
        return "";
    }

}

?>
