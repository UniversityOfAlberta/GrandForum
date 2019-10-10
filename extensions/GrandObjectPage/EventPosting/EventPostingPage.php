<?php

BackbonePage::register('EventPostingPage', 'Event Posting', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'EventPostingPage::createToolboxLinks';

class EventPostingPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'eventpostings',
                     'eventposting',
                     'eventposting_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'EventPostingsView',
                     'EventPostingView',
                     'EventPostingEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $toolbox['Postings']['links'][] = TabUtils::createToolboxLink("Event Postings", "$wgServer$wgScriptPath/index.php/Special:EventPostingPage");
        }
        return true;
    }

}

?>
