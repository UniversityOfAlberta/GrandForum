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
    
    function getTemplates(){
        global $wgOut;
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
