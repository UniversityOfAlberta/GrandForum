<?php

BackbonePage::register('FreezePage', 'Freeze', 'network-tools', dirname(__FILE__));

$wgHooks['SubLevelTabs'][] = 'FreezePage::createSubTabs';

class FreezePage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
	    return $person->isRoleAtLeast(STAFF);
    }
    
    function getTemplates(){
        return array('freeze');
    }
    
    function getViews(){
        return array('FreezeView');
    }
    
    function getModels(){
        return array();
    }
    
    static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle;
	    $person = Person::newFromWgUser();
	    if($person->isRoleAtLeast(STAFF)){
	        $selected = @($wgTitle->getText() == "FreezePage") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Project Freeze", "$wgServer$wgScriptPath/index.php/Special:FreezePage", $selected);
	    }
	    return true;
    }

}

?>
