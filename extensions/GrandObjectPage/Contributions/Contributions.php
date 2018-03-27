<?php

BackbonePage::register('Contributions', 'Contributions', 'network-tools', dirname(__FILE__));
$wgHooks['ToolboxLinks'][] = 'Contributions::createToolboxLinks';

class Contributions extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'contributions',
                     'contribution',
                     'contribution_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'ContributionsView',
                     'ContributionView',
                     'ContributionEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(HQP)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage Contributions", "$wgServer$wgScriptPath/index.php/Special:Contributions");
	    }
	    return true;
	}

}

?>
