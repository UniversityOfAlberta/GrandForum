<?php

class PersonUofANewsTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("News");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $news = UofANews::getNewsForPerson($this->person);
        if(count($news) > 0){
            foreach($news as $article){
                $this->html .= "<div style='display: flex; border: 1px solid #EEEEEE; padding: 0 15px; margin-bottom: 15px;'>";
                
                $this->html .= "<div style='width: 100%; padding-right: 20px;'><h3><a href='{$article->getUrl()}' target='_blank'>{$article->getTitle()}</a></h3>";
                $this->html .= "<p>{$article->getFirstSentences()}</p>";
                $this->html .= "<p>Published: {$article->getDate()}</p></div>";
                if($article->getImg() != ""){
                    $this->html .= "<div style='width: 200px;'><img src='{$article->getImg()}' style='max-width: 200px; max-height: 200px; margin: 20px 0 20px 0;' /></div>";
                }
                $this->html .= "</div>";
            }
        }
        return $this->html;
    }

}
?>
