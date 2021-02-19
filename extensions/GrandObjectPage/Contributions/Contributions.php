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
        global $wgOut;
        $wgOut->addScript("<script type='text/javascript'>
            var cashMap = ".json_encode(Contribution::$cashMap).";
            var inkindMap = ".json_encode(Contribution::$inkindMap).";
        </script>");
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
