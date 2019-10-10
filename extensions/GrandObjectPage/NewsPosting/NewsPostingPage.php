<?php

BackbonePage::register('NewsPostingPage', 'News Posting', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'NewsPostingPage::createToolboxLinks';

class NewsPostingPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'newspostings',
                     'newsposting',
                     'newsposting_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'NewsPostingsView',
                     'NewsPostingView',
                     'NewsPostingEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $toolbox['Postings']['links'][] = TabUtils::createToolboxLink("News Postings", "$wgServer$wgScriptPath/index.php/Special:NewsPostingPage");
        }
        return true;
    }

}

?>
