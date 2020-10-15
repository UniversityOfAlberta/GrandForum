<?php

BackbonePage::register('BSIPostingPage', 'BSI Posting', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'BSIPostingPage::createToolboxLinks';

class BSIPostingPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'bsipostings',
                     'bsiposting',
                     'bsiposting_edit');
    }
    
    function getViews(){
        global $wgOut;
        $departments = json_encode(array_values(Person::getAllDepartments()));
        
        $wgOut->addScript("<script type='text/javascript'>
            var allDepartments = $departments;
        </script>");
    
        return array('Backbone/*',
                     'BSIPostingsView',
                     'BSIPostingView',
                     'BSIPostingEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $toolbox['Postings']['links'][] = TabUtils::createToolboxLink("BSI Postings", "$wgServer$wgScriptPath/index.php/Special:BSIPostingPage");
        }
        return true;
    }

}

?>
