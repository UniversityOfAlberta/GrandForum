<?php

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
        return array();
    }

}

?>
