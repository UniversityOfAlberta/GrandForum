<?php

BackbonePage::register('JobPostingPage', 'Job Posting', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'JobPostingPage::createToolboxLinks';

class JobPostingPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'jobpostings',
                     'jobposting',
                     'jobposting_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'JobPostingsView',
                     'JobPostingView',
                     'JobPostingEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $toolbox['Postings']['links'][] = TabUtils::createToolboxLink("Job Postings", "$wgServer$wgScriptPath/index.php/Special:JobPostingPage");
        }
        return true;
    }

}

?>
