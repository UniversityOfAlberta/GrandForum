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
                    AND (u.upload_name = REPLACE(p.page_title, '_', ' ') OR u.upload_name = REPLACE(CONCAT('File:', p.page_title), '_', ' '))";
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
	
	function __construct($article){
		$this->id = $article->getId();
		$this->ns = $article->getTitle()->getNsText();
		$this->title = $article->getTitle()->getText();
		$this->url = $article->getTitle()->getFullURL();
		$this->article = $article;
	}
	
	function getText(){
	    return $this->article->getContent();
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
	
	function exists(){
        return $this->article->exists();
	}
	
	function getCacheId(){
	    global $wgSitename;
	}
}
?>
