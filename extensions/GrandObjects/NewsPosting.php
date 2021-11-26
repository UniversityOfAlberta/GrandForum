<?php

/**
 * @package GrandObjects
 */

class NewsPosting extends Posting {
    
    static $dbTable = 'grand_news_postings';

    var $author;
    var $sourceName;
    var $sourceLink;
    var $enableRegistration;
    var $enableMaterials;
    
    function NewsPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->author = $row['author'];
            $this->sourceName = $row['source_name'];
            $this->sourceLink = $row['source_link'];
            $this->enableRegistration = $row['enable_registration'];
            $this->enableMaterials = $row['enable_materials'];
        }
    }
    
    function getAuthor(){
        return $this->author;
    }
    
    function getSourceName(){
        return $this->sourceName;
    }
    
    function getSourceLink(){
        return $this->sourceLink;
    }
    
    function isRegistrationEnabled(){
        return ($this->enableRegistration == 1);
    }
    
    function isMaterialSubmissionEnabled(){
        return ($this->enableMaterials == 1);
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['author'] = $this->getAuthor();
        $json['sourceName'] = $this->getSourceName();
        $json['sourceLink'] = $this->getSourceLink();
        $json['enableRegistration'] = $this->isRegistrationEnabled();
        $json['enableMaterials'] = $this->isMaterialSubmissionEnabled();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('author' => $this->author,
                                                'source_name' => $this->sourceName,
                                                'source_link' => $this->sourceLink,
                                                'enable_registration' => $this->enableRegistration,
                                                'enable_materials' => $this->enableMaterials),
                                          array('id' => $this->id));
        }
        return $status;
    }
    
    function update(){
        $status = parent::update();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('author' => $this->author,
                                                'source_name' => $this->sourceName,
                                                'source_link' => $this->sourceLink,
                                                'enable_registration' => $this->enableRegistration,
                                                'enable_materials' => $this->enableMaterials),
                                          array('id' => $this->id));
        }
        return $status;
    }
}

?>
