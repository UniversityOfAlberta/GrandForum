<?php

/**
 * @package GrandObjects
 */
 
use MediaWiki\MediaWikiServices;

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
	
	function __construct($article){
		$this->id = $article->getId();
		$this->ns = $article->getTitle()->getNsText();
		$this->title = $article->getTitle()->getText();
		$this->url = $article->getTitle()->getFullURL();
		$this->article = $article;
	}
	
	function getText(){
	    global $wgUser;
	    $parser = MediaWikiServices::getInstance()->getParser();
	    $content = $this->article->getPage()->getContent();
	    $text = "";
	    if($content != null){
	        $text = $content->getText();
	        $text = $parser->parse($text, $this->article->getTitle(), new ParserOptions($wgUser))->getText();
	    }
	    return $text;
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
