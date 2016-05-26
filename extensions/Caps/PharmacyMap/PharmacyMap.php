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
		     'pharmacy_add',
		    );
    }
    
    function getViews(){
        return array('Backbone/*',
		     'PharmacyMapView',
		     'PharmacyAddView',
		    );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser,$wgOut,$wgLang;
        $title_locate = "Locate a Pharmacy";
        if($wgLang->getCode() == "fr"){
             $title_locate = "Localiser une Pharmacie";
        }
        $title_add = "Add a Pharmacy";
        if($wgLang->getCode() == "fr"){
             $title_add = "Ajouter une Pharmacie";
        }
        if(self::userCanExecute($wgUser)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink($title_locate, "$wgServer$wgScriptPath/index.php/Special:PharmacyMap");
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink($title_add, "$wgServer$wgScriptPath/index.php/Special:PharmacyMap#/add");

        }
        return true;
    }
}

?>
