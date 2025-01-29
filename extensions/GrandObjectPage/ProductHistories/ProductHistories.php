<?php

$wgHooks['SubLevelTabs'][] = 'ProductHistories::createSubTabs';
BackbonePage::register('ProductHistories', 'ProductHistories', 'network-tools', dirname(__FILE__));

class ProductHistories extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(STAFF);
    }
    
    function getTemplates(){
        return array('product_histories',
                     'person_select',
                     'histories',
                     'history');
    }
    
    function getViews(){
        return array('ProductHistoriesView');
    }
    
    function getModels(){
        global $wgOut;
        $people = new Collection(Person::filterFaculty(Person::getAllPeople(CI)));
        $wgOut->addHTML("<script type='text/javascript'>
            var people = ".$people->toJSON().";
        </script>");
        return array();
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "ProductHistories")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Product Histories", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:ProductHistories", 
                                                                   "$selected");
        }
    }

}

?>
