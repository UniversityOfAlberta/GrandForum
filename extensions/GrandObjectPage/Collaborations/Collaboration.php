<?php

BackbonePage::register('CollaborationPage', 'CollaborationPage', 'network-tools', dirname(__FILE__));
$wgHooks['ToolboxLinks'][] = 'CollaborationPage::createToolboxLinks';
class CollaborationPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'MyThreads/*',
                     'Products/product',
                     'collaborations',
                     'collaboration',
                     'collaboration_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'MyThreads/*',
                     'Products/ProductView',
                     'CollaborationsView',
                     'CollaborationView',
                     'CollaborationEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP)){
            $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage Collaborations and Knowledge Users", "$wgServer$wgScriptPath/index.php/Special:CollaborationPage");
        }
        return true;
    }

}

?>
