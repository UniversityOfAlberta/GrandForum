<?php
$wgHooks['ToolboxLinks'][] = 'PharmacyMap::createToolboxLinks';
BackbonePage::register('PharmacyMap', 'PharmacyMap', 'network-tools', dirname(__FILE__));

class PharmacyMap extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(NI);
    }
    
    function getTemplates(){
        return array('Backbone/*',
		     'pharmacy_map',
		    );
    }
    
    function getViews(){
        return array('Backbone/*',
		     'PharmacyMapView',
		    );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser,$wgOut;
        if(self::userCanExecute($wgUser)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Locate a Pharmacy", "$wgServer$wgScriptPath/index.php/Special:PharmacyMap");
        }
        return true;
    }
}

?>
