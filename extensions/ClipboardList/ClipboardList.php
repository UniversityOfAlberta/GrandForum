<?php

BackbonePage::register('ClipboardList', 'ClipboardList', 'network-tools', dirname(__FILE__));

class ClipboardList extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
    
    static function getCategoryJSON(){
        $dir = dirname(__FILE__) . '/';
        $json = json_decode(file_get_contents("{$dir}categories.json"));
        return $json;
    }
    
    function getTemplates(){
        global $wgOut;
        $json = self::getCategoryJSON();
        $wgOut->addHTML("<script type='text/javascript'>
            var cat_json = ".json_encode($json).";
        </script>");
        return array('Backbone/*',
		     'clipboardlist',
		     'clipboardlist_row',
		    );
    }
    
    function getViews(){
        return array('Backbone/*',
		     'ClipboardListView',
		     'ClipboardListRowView',
		    );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
}

?>
