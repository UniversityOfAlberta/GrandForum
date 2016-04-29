<?php
$wgHooks['ToolboxLinks'][] = 'StoryManagePage::createToolboxLinks';
BackbonePage::register('StoryManagePage', 'StoryManagePage', 'network-tools', dirname(__FILE__));

class StoryManagePage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(NI);
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
        global $wgServer, $wgScriptPath, $wgUser;
        if(self::userCanExecute($wgUser)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("My Cases", "$wgServer$wgScriptPath/index.php/Special:StoryManagePage");
        }
        return true;
    }
}

?>
