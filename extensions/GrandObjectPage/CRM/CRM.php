<?php

BackbonePage::register('CRM', 'CRM', 'network-tools', dirname(__FILE__));

class CRM extends BackbonePage {
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'crm_contacts_table',
                     'crm_contact',
                     'crm_contact_edit',
                     'crm_opportunity',
                     'crm_opportunity_edit',
                     'crm_task',
                     'crm_task_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'CRMContactsTableView',
                     'CRMContactView',
                     'CRMContactEditView',
                     'CRMOpportunityView',
                     'CRMOpportunityEditView',
                     'CRMTaskView',
                     'CRMTaskEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
