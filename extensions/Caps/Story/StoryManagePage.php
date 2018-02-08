<?php
$wgHooks['ToolboxLinks'][] = 'StoryManagePage::createToolboxLinks';
BackbonePage::register('StoryManagePage', 'StoryManagePage', 'network-tools', dirname(__FILE__));

class StoryManagePage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isLoggedIn();
    }
    
    function getTemplates(){
        return array('Backbone/*',
		     'manage_stories',
		     'manage_stories_row',
		     'story',
		     'story_edit',
		     'comment');
    }
    
    function getViews(){
        return array('Backbone/*',
		     'ManageStoriesView',
		     'ManageStoriesRowView',
		     'StoryView',
		     'StoryEditView',
		     'CommentView');
    }
    
    function getModels(){
        return array('Backbone/*',
		     'StoryPageModel');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser,$wgLang;
        $me = Person::newFromWgUser();
        $title_add = "Discussion room";
        if($wgLang->getCode() == "fr"){
             $title_add = "Salle de discussion";
        }
        if($me->isLoggedIn()){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink($title_add, "$wgServer$wgScriptPath/index.php/Special:StoryManagePage");
        }
        return true;
    }
}

?>
