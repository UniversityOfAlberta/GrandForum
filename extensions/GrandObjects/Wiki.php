<?php

/**
 * @package GrandObjects
 */

class Wiki extends BackboneModel {

    var $id;
    var $ns;
    var $title;
    var $url;
    var $text;
    var $article;
    
    static function newFromId($id){
        $article = Article::newFromId($id);
        return new Wiki($article);
    }
    
    static function newFromTitle($text){
        $title = Title::newFromText("$text");
        $article = new Article($title);
        return new Wiki($article);
    }

    static function getWikiPages($namespace){
        $articles = array();
        $data = DBFunctions::select(array('mw_an_extranamespaces'),
                    array('nsId'),
                    array('nsName'=>"{$namespace}_Wiki"));
        if(count($data)>0){
            $nsId = $data[0]['nsId'];
            $sql = "SELECT page_id
                        FROM mw_page
                        WHERE page_namespace = '$nsId'";
                $data = DBFunctions::execSQL($sql);
                $articles = array();
                foreach($data as $row){
                    $article = Article::newFromId($row['page_id']);
                    if($article != null && strstr($article->getTitle()->getText(), "MAIL ") === false){
                        $articles[] = $article;
                    }
                }
        }
        return $articles;
    }

    static function getFiles($namespace){
        $sql = "SELECT p.page_id
                FROM mw_an_upload_permissions u, mw_page p
                WHERE u.nsName = REPLACE('{$namespace}', ' ', '_')
                AND (REPLACE(u.upload_name, '_', ' ') = REPLACE(p.page_title, '_', ' ') OR REPLACE(u.upload_name, '_', ' ') = REPLACE(CONCAT('File:', p.page_title), '_', ' '))";
        $data = DBFunctions::execSQL($sql);
        $articles = array();
        foreach($data as $row){
            $article = Article::newFromId($row['page_id']);
            if($article != null){
                $articles[] = $article;
            }
        }
        return $articles;
    }
    
    function Wiki($article){
        if($article != null){
            $this->id = $article->getId();
            $this->ns = $article->getTitle()->getNsText();
            $this->title = $article->getTitle()->getText();
            $this->url = $article->getTitle()->getFullURL();
            $this->article = $article;
        }
    }

    function getId(){
        return $this->id;
    }

    function getText(){
        return $this->article->getContent();
    }

    function getTitle(){
        return $this->title;
    }

    function getUrl(){
        return $this->url;
    }

    function getArticle(){
        return $this->article;
    }

    function getNewestAuthor(){
        $data = DBFunctions::select(array("mw_revision"),
                                    array("rev_user"),
                                    array("rev_page"=>$this->getId()),
                                    array("rev_id"=>"DESC"));
        if(count($data)>0){
        return Person::newFromId($data[0]['rev_user']);
        }
        return null;
    }

    function toArray(){
        $json = array('id' => $this->getId(),
                      'ns' => $this->ns,
                      'title' => $this->title,
                      'url' => $this->url,
                      'text' => $this->getText());
        return $json;
    }
    
    function create(){
        
    }
    
    function update(){
        
    }
    
    function delete(){
        
    }

    static function getAllUnapprovedPages(){
        $pages = array();
            $data = DBFunctions::select(array("grand_page_approved"),
                                        array("page_id"),
                                        array("approved"=>EQ(COL(0))));
        foreach($data as $row){
        $pages[] = Wiki::newFromId($row['page_id']);
        }
            
        return $pages;
    }
    
    function exists(){
        return $this->article->exists();
    }
    
    function getCacheId(){
        global $wgSitename;
    }

    function isApproved(){
            $data = DBFunctions::select(array("grand_page_approved"=>"a",
                                              "mw_revision"=>"r"),
                                        array("a.approved", "r.rev_user"),
                                        array("a.page_id"=>EQ(COL("r.rev_page")),
                                              "r.rev_page"=>$this->getId(),
                          "a.approved"=>1),
                                        array("rev_id"=>"DESC"));
        return (count($data)>0);

    }

    function canView(){
        $me = Person::newFromWgUser();
        $data = DBFunctions::select(array("grand_page_approved"=>"a", 
                          "mw_revision"=>"r"),
                    array("a.approved", "r.rev_user"),
                    array("a.page_id"=>EQ(COL("r.rev_page")),
                          "r.rev_page"=>$this->getId()), 
                    array("rev_id"=>"DESC"));
        if (count($data)>0){
        return ($data[0]['approved'] || $me->getId() === $data[0]['rev_user'] || $me->isRoleAtLeast(STAFF));
        }
        return true;
    }
}
?>
