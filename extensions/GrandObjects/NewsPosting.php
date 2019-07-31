<?php

/**
 * @package GrandObjects
 */

class NewsPosting extends BackboneModel {
    
    var $id;
    var $translatedId;
    var $userId;
    var $visibility;
    var $language;
    var $title;
    var $articleLink;
    var $postedDate;
    var $summary;
    var $author;
    var $sourceName;
    var $sourceLink;
    var $image;
    var $imageCaption;
    var $created;
    var $deleted;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_news_postings'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $news = new NewsPosting($data);
        if($news->isAllowedToView()){
            return $news;
        }
        else{
            return new NewsPosting(array());
        }
    }
    
    /**
     * Returns an array of all News Postings which this user is able to view
     */
    static function getAllNewsPostings(){
        $data = DBFunctions::select(array('grand_news_postings'),
                                    array('*'),
                                    array('deleted' => EQ(0)));
        $newses = array();
        foreach($data as $row){
            $news = new NewsPosting(array($row));
            if($news->isAllowedToView()){
                $newses[] = $news;
            }
        }
        return $newses;
    }
    
    /**
     * Returns an array of News Postings which have not yet expired
     */
    static function getCurrentNewsPostings(){
        $newNewses = array();
        $newses = self::getAllNewsPostings();
        foreach($newses as $news){
            if($news->getDeadlineType() == "Open" || $news->getDeadlineDate() >= date('Y-m-d')){
                $newNewses[] = $news;   
            }
        }
        return $newNewses;
    }
    
    function NewsPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->translatedId = $row['translated_id'];
            $this->userId = $row['user_id'];
            $this->visibility = $row['visibility'];
            $this->language = $row['language'];
            $this->title = $row['title'];
            $this->articleLink = $row['article_link'];
            $this->postedDate = $row['posted_date'];
            $this->summary = $row['summary'];
            $this->author = $row['author'];
            $this->sourceName = $row['source_name'];
            $this->sourceLink = $row['source_link'];
            $this->image = $row['image'];
            $this->imageCaption = $row['image_caption'];
            $this->created = $row['created'];
            $this->deleted = $row['deleted'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getTranslatedId(){
        return $this->translatedId;
    }
    
    function getUserId(){
        return $this->userId;
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
    
    function getArticleLink(){
        return $this->articleLink;
    }
    
    function getPostedDate(){
        return $this->postedDate;
    }
    
    function getSummary(){
        return $this->summary;
    }
    
    function getAuthor(){
        return $this->author;
    }
    
    function getSourceName(){
        return $this->sourceName;
    }
    
    function getImage(){
        return $this->image;
    }
    
    function getImageCaption(){
        return $this->imageCaption;
    }
    
    function getCreated(){
        return $this->created;
    }
    
    function isDeleted(){
        return $this->deleted;
    }
    
    /**
     * Returns the url of this NewsPosting's page
     * @return string The url of this NewsPosting's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:NewsPostingPage#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:NewsPostingPage?embed#/{$this->getId()}";
    }
    
    function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->getUserId() || $me->isRoleAtLeast(STAFF));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($this->getVisibility() == "Publish"){
            // News is Public
            return true;
        }
        if($me->getId() == $this->getUserId()){
            // News was created by the logged in user
            return true;
        }
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return ($me->isLoggedIn() && ($me->isRoleAtLeast(MANAGER) || $me->isRole(PL) || $me->isRole(PA)));
    }
    
    function toArray(){
        global $wgUser;
        $project = null;
        $proj = $this->getProject();
        if($proj != null){
            $project = $proj->toArray();
        }
        $json = array('id' => $this->getId(),
                      'translatedId' => $this->getTranslatedId(),
                      'userId' => $this->getUserId(),
                      'visibility' => $this->getVisibility(),
                      'language' => $this->getLanguage(),
                      'title' => $this->getTitle(),
                      'articleLink' => $this->getArticleLink(),
                      'postedDate' => $this->getPostedDate(),
                      'summary' => $this->getSummary(),
                      'author' => $this->getAuthor(),
                      'sourceName' => $this->getSourceName(),
                      'sourceLink' => $this->getSourceLink(),
                      'image' => $this->getImage(),
                      'imageCaption' => $this->getImageCaption(),
                      'created' => $this->getCreated(),
                      'deleted' => $this->isDeleted(),
                      'isAllowedToEdit' => $this->isAllowedToEdit(),
                      'url' => $this->getUrl());
        return $json;
    }
    
    function create(){
        if(self::isAllowedToCreate()){
            $status = DBFunctions::insert('grand_news_postings',
                                          array('user_id' => $this->userId,
                                                'translated_id' => $this->translatedId,
                                                'user_id' => $this->userId,
                                                'visibility' => $this->visibility,
                                                'language' => $this->language,
                                                'title' => $this->title,
                                                'article_link' => $this->articleLink,
                                                'posted_date' => $this->postedDate,
                                                'summary' => $this->summary,
                                                'author' => $this->author,
                                                'source_name' => $this->sourceName,
                                                'source_link' => $this->sourceLink,
                                                'image' => $this->image,
                                                'image_caption' => $this->imageCaption,
                                                'created' => $this->created,
                                                'deleted' => $this->deleted));
            if($status){
                $this->id = DBFunctions::insertId();
            }
            return $status;
        }
        return false;
    }
    
    function update(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update('grand_news_postings',
                                          array('user_id' => $this->userId,
                                                'translated_id' => $this->translatedId,
                                                'user_id' => $this->userId,
                                                'visibility' => $this->visibility,
                                                'language' => $this->language,
                                                'title' => $this->title,
                                                'article_link' => $this->articleLink,
                                                'posted_date' => $this->postedDate,
                                                'summary' => $this->summary,
                                                'author' => $this->author,
                                                'source_name' => $this->sourceName,
                                                'source_link' => $this->sourceLink,
                                                'image' => $this->image,
                                                'image_caption' => $this->imageCaption,
                                                'created' => $this->created,
                                                'deleted' => $this->deleted),
                                          array('id' => $this->id));
            return $status;
        }
        return false;
    }
    
    function delete(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update('grand_news_postings',
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
