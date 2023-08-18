<?php

BackbonePage::register('CRM', 'CRM', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'CRM::createSideBarLink';

class CRM extends BackbonePage {
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return $me->isRoleAtLeast(STAFF);
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'crm_contacts_table',
                     'crm_project_contacts_table',
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
                     'CRMProjectContactsTableView',
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
    
    static function createSideBarLink(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $link = TabUtils::createToolboxLink("CRM", "$wgServer$wgScriptPath/index.php/Special:CRM");
            $toolbox['Other']['links'][] = $link;
        }
        return true;
    }

}

?>
