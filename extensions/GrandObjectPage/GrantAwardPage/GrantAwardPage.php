<?php

$wgHooks['ToolboxLinks'][] = 'GrantAwardPage::createToolboxLinks';
BackbonePage::register('GrantAwardPage', 'Grant Award', 'network-tools', dirname(__FILE__));

class GrantAwardPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('grantaward',
                     'grantawards',
                     'edit_grantaward');
    }
    
    function getViews(){
        return array('GrantAwardView',
                     'GrantAwardsView',
                     'EditGrantAwardView');
    }
    
    function getModels(){
        return array();
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config, $wgUser;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(NI)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Grant Awards", 
	                                                                      "$wgServer$wgScriptPath/index.php/Special:GrantAwardPage");
	    }
	    return true;
	}

}

?>
