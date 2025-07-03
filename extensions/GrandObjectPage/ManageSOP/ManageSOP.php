<?php

$wgHooks['ToolboxLinks'][] = 'ManageSOP::createToolboxLinks';
BackbonePage::register('ManageSOP', 'Manage SOP', 'network-tools', dirname(__FILE__));

class ManageSOP extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'Products/*',
                     'manage_sop',
                     'manage_sop_row');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'Products/*',
                     'ManageSOPView',
                     'ManageSOPRowView');
    }
    
    function getModels(){
        global $wgOut;
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config, $wgUser;
	    if((new self)->userCanExecute($wgUser)){
	        $toolbox['Other']['links'][] = TabUtils::createToolboxLink("SOP", "$wgServer$wgScriptPath/index.php/Special:ManageSOP");
	    }
	    return true;
	}
	
	function execute($par){
	    parent::execute($par);
	}

}

?>
