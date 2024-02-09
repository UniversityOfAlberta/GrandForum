<?php

class PersonStoriesTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonStoriesTab($person, $visibility){
        parent::AbstractTab("Stories");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $stories = array_reverse($this->person->getStories());
        if(count($stories) > 0){
            foreach($stories as $story){
                $this->html .= "<p class='thread-message'>{$story}</p>";
            }
        }
        return $this->html;
    }

}
?>
