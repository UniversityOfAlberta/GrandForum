<?php

$wgHooks['ToolboxLinks'][] = 'Keywords::createToolboxLinks';
BackbonePage::register('Keywords', 'Keywords', 'network-tools', dirname(__FILE__));

class Keywords extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
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
	    $me = Person::newFromWgUser();
	    if($me->isRole('View Profile') || $me->isRoleAtLeast(MANAGER)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage Keywords", 
	                                                                      "$wgServer$wgScriptPath/index.php/Special:Keywords");
	    }
	    return true;
	}

}

?>
