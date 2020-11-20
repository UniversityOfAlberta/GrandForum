<?php

BackbonePage::register('CRM', 'CRM', 'network-tools', dirname(__FILE__));

class CRM extends BackbonePage {
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'crm_contacts_table',
                     'crm_contact');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'CRMContactsTableView',
                     'CRMContactView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
