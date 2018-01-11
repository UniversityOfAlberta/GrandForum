<?php

BackbonePage::register('Contributions', 'Contributions', 'network-tools', dirname(__FILE__));

class Contributions extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'contributions',
                     'contribution',
                     'contribution_edit',
                     'ManageProducts/manage_products_other_popup',
                     'ManageProducts/manage_products_projects_popup');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'ContributionsView',
                     'ContributionView',
                     'ContributionEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
