<?php
BackbonePage::register('StoryPage', 'StoryPage', 'network-tools', dirname(__FILE__));

class StoryPage extends BackbonePage {

    function userCanExecute($user){
        return true;
    }

    function getTemplates(){
        return array('Backbone/*',
		     'story_edit',
		     'story');
    }

    function getViews(){
        return array('Backbone/*',
		     'StoryEditView',
		     'StoryView');
    }

    function getModels(){
        return array('Backbone/*');
    }

}

?>
