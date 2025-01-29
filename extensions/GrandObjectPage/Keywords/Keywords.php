<?php

$wgHooks['ToolboxLinks'][] = 'Keywords::createToolboxLinks';
BackbonePage::register('Keywords', 'Grants (Admin View)', 'network-tools', dirname(__FILE__));

class Keywords extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
	    return ($me->isSubRole('ViewProfile') || $me->isRoleAtLeast(MANAGER));
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'grant',
                     'grants',
                     'edit_grant');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'GrantView',
                     'GrantsView',
                     'EditGrantView');
    }
    
    function getModels(){
        global $wgOut;
        $wgOut->addHTML("<script type='text/javascript'>
            var allKeywords = ".json_encode(Keyword::getAllEnteredKeywords()).";
            var allPartners = ".json_encode(Keyword::getAllEnteredPartners()).";
        </script>");
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config, $wgUser;
	    if((new self)->userCanExecute($wgUser)){
	        $toolbox['Tools']['links'][] = TabUtils::createToolboxLink("Manage Keywords", 
	                                                                   "$wgServer$wgScriptPath/index.php/Special:Keywords");
	    }
	    return true;
	}

}

?>
