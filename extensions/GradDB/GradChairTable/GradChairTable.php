<?php

$wgHooks['SubLevelTabs'][] = 'GradChairTable::createSubTabs';
BackbonePage::register('GradChairTable', 'GradChairTable', 'network-tools', dirname(__FILE__));

class GradChairTable extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(STAFF);
    }
    
    function getTemplates(){
        return array('grad_chair_table',
                     'grad_chair_row');
    }
    
    function getViews(){
        return array('GradChairTableView');
    }
    
    function getModels(){
        return array();
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if(self::userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "GradChairTable")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Grad Chair Table", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:GradChairTable", 
                                                                   "$selected");
        }
    }

}

?>
