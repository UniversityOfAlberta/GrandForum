<?php

//$wgHooks['ToolboxLinks'][] = 'GrantAwardPage::createToolboxLinks';
BackbonePage::register('GrantAwardPage', 'Awarded NSERC Applications', 'network-tools', dirname(__FILE__));

class GrantAwardPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'grantaward',
                     'grantawards',
                     'edit_grantaward',
                     'edit_partner');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'GrantAwardView',
                     'GrantAwardsView',
                     'EditGrantAwardView',
                     'EditPartnerView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config, $wgUser;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(NI)){
	        $toolbox['Tools']['links'][] = TabUtils::createToolboxLink("Grant Awards", 
	                                                                   "$wgServer$wgScriptPath/index.php/Special:GrantAwardPage");
	    }
	    return true;
	}

}

?>
