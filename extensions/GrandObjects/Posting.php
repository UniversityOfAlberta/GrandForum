<?php

/**
 * @package GrandObjects
 */

class Posting extends BackboneModel {
    
    static $dbTable = "";
    
    var $id;
    var $userId;
    var $visibility;
    var $language;
    var $title;
    var $titleFr;
    var $articleLink;
    var $startDate;
    var $endDate;
    var $summary;
    var $summaryFr;
    var $image;
    var $imageCaption;
    var $imageCaptionFr;
    var $previewCode;
    var $created;
    var $modified;
    var $deleted;
    
    static function newFromId($id){
        $data = DBFunctions::select(array(static::$dbTable),
                                    array('*'),
                                    array('id' => EQ($id)));
        $posting = new static($data);
        if($posting->isAllowedToView()){
            return $posting;
        }
        else{
            return new self(array());
        }
    }
    
    /**
     * Returns an array of all Postings which this user is able to view
     */
    static function getAllPostings(){
        $data = DBFunctions::select(array(static::$dbTable),
                                    array('*'),
                                    array('deleted' => EQ(0)));
        $postings = array();
        foreach($data as $row){
            $posting = new static(array($row));
            if(isset($_GET['apiKey']) && $posting->visibility != "Publish"){
                // Accessed using API Key, so restrict to Published only
                continue;
            }
            if($posting->isAllowedToView()){
                $postings[] = $posting;
            }
        }
        return $postings;
    }
    
    /**
     * Returns an array of Postings which have not yet expired
     */
    static function getCurrentPostings(){
        return static::getAllPostings();
    }
    
    /**
     * Returns an array of Postings that have been modified since the specified date
     */
    static function getNewPostings($date){
        $postings = static::getAllPostings();
        $return = array();
        foreach($postings as $posting){
            if($posting->modified >= $date){
                $return[] = $posting;
            }
        }
        return $return;
    }
    
    function Posting($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->userId = $row['user_id'];
            $this->visibility = $row['visibility'];
            $this->language = $row['language'];
            $this->title = $row['title'];
            $this->titleFr = $row['title_fr'];
            $this->articleLink = $row['article_link'];
            $this->startDate = $row['start_date'];
            $this->endDate = $row['end_date'];
            $this->summary = $row['summary'];
            $this->summaryFr = $row['summary_fr'];
            $this->image = $row['image'];
            $this->imageCaption = $row['image_caption'];
            $this->imageCaptionFr = $row['image_caption_fr'];
            $this->previewCode = $row['preview_code'];
            $this->created = $row['created'];
            $this->modified = $row['modified'];
            $this->deleted = $row['deleted'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->userId;
    }
    
    function getUser(){
        return Person::newFromId($this->getUserId());
    }
    
    function getVisibility(){
        return $this->visibility;
    }
    
    function getLanguage(){
        return $this->language;
    }
    
    function getTitle(){
        return $this->title;
    }
    
    function getTitleFr(){
        return $this->titleFr;
    }
    
    function getArticleLink(){
        return $this->articleLink;
    }
    
    function getStartDate(){
        return substr($this->startDate, 0, 10);
    }
    
    function getEndDate(){
        return substr($this->endDate, 0, 10);
    }
    
    function getSummary(){
        return $this->summary;
    }
    
    function getSummaryFr(){
        return $this->summaryFr;
    }
    
    function getImage(){
        return $this->image;
    }
    
    function getImageUrl(){
        global $wgServer, $wgScriptPath;
        $image = $this->getImage();
        if($image != ""){
            $md5 = md5($this->getImage());
            $class = strtolower(get_class($this));
            return "{$wgServer}{$wgScriptPath}/index.php?action=api.{$class}/{$this->getId()}/image/$md5";
        }
        return "";
    }
    
    function getImageCaption(){
        return $this->imageCaption;
    }
    
    function getImageCaptionFr(){
        return $this->imageCaptionFr;
    }
    
    function getPreviewCode(){
        return $this->previewCode;
    }
    
    function getCreated(){
        return $this->created;
    }
    
    function getModified(){
        return $this->modified;
    }
    
    function isDeleted(){
        return $this->deleted;
    }
    
    function generatePreviewCode(){
        $this->previewCode = md5(microtime() + rand(0,1000));
        DBFunctions::update(static::$dbTable,
                            array('preview_code' => $this->previewCode),
                            array('id' => $this->id));
    }
    
    /**
     * Returns the url of this Posting's page
     * @return string The url of this Posting's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        $class = get_class($this);
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:{$class}Page#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:{$class}Page?embed#/{$this->getId()}";
    }
    
    function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->getUserId() || $me->isRoleAtLeast(STAFF));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($this->getVisibility() == "Publish"){
            // Posting is Public
            return true;
        }
        if(($me->getId() == $this->getUserId() && !isset($_GET['apiKey'])) ||  
           ($me->isRoleAtLeast(STAFF) && $this->getPreviewCode() == @$_GET['previewCode']) ||
           ($me->isRoleAtLeast(STAFF) && !isset($_GET['apiKey']))){
            // Posting was created by the logged in user (or is Staff)
            return true;
        }
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return ($me->isLoggedIn() && ($me->isRoleAtLeast(MANAGER) || $me->isRole(PL) || $me->isRole(PA)));
    }
    
    function toArray(){
        $json = array('id' => $this->getId(),
                      'userId' => $this->getUserId(),
                      'user' => $this->getUser()->toArray(),
                      'visibility' => $this->getVisibility(),
                      'language' => $this->getLanguage(),
                      'title' => $this->getTitle(),
                      'titleFr' => $this->getTitleFr(),
                      'articleLink' => $this->getArticleLink(),
                      'startDate' => $this->getStartDate(),
                      'endDate' => $this->getEndDate(),
                      'summary' => $this->getSummary(),
                      'summaryFr' => $this->getSummaryFr(),
                      'image' => $this->getImageUrl(),
                      'imageCaption' => $this->getImageCaption(),
                      'imageCaptionFr' => $this->getImageCaptionFr(),
                      'created' => $this->getCreated(),
                      'modified' => $this->getModified(),
                      'deleted' => $this->isDeleted(),
                      'previewCode' => $this->getPreviewCode(),
                      'isAllowedToEdit' => $this->isAllowedToEdit(),
                      'url' => $this->getUrl());
        return $json;
    }
    
    function create(){
        if(self::isAllowedToCreate()){
            $status = DBFunctions::insert(static::$dbTable,
                                          array('user_id' => $this->userId,
                                                'visibility' => $this->visibility,
                                                'language' => $this->language,
                                                'title' => $this->title,
                                                'title_fr' => $this->titleFr,
                                                'article_link' => $this->articleLink,
                                                'start_date' => $this->startDate,
                                                'end_date' => $this->endDate,
                                                'summary' => $this->summary,
                                                'summary_fr' => $this->summaryFr,
                                                'image' => $this->image,
                                                'image_caption' => $this->imageCaption,
                                                'image_caption_fr' => $this->imageCaptionFr,
                                                'modified' => EQ(COL('CURRENT_TIMESTAMP')),
                                                'deleted' => $this->deleted));
            if($status){
                $this->id = DBFunctions::insertId();
                $this->generatePreviewCode();
            }
            return $status;
        }
        return false;
    }
    
    function update(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update(static::$dbTable,
                                          array('user_id' => $this->userId,
                                                'visibility' => $this->visibility,
                                                'language' => $this->language,
                                                'title' => $this->title,
                                                'title_fr' => $this->titleFr,
                                                'article_link' => $this->articleLink,
                                                'start_date' => $this->startDate,
                                                'end_date' => $this->endDate,
                                                'summary' => $this->summary,
                                                'summary_fr' => $this->summaryFr,
                                                'image' => $this->image,
                                                'image_caption' => $this->imageCaption,
                                                'image_caption_fr' => $this->imageCaptionFr,
                                                'modified' => EQ(COL('CURRENT_TIMESTAMP')),
                                                'deleted' => $this->deleted),
                                          array('id' => $this->id));
            $this->generatePreviewCode();
            return $status;
        }
        return false;
    }
    
    function delete(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update(static::$dbTable,
                                          array('deleted' => 1),
                                          array('id' => $this->id));
            if($status){
                $this->deleted = true;
            }
            return $status;
        }
        return false;
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        global $wgSitename;
    }
}

?>
