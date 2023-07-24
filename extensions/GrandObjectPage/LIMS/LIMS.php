<?php

require_once("KPISummary.php");

BackbonePage::register('LIMS', 'LIMS', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'LIMS::createSideBarLink';

class LIMS extends BackbonePage {
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return $me->isRoleAtLeast(STAFF);
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'lims_contacts_table',
                     'lims_contact',
                     'lims_contact_edit',
                     'lims_opportunity',
                     'lims_opportunity_edit',
                     'lims_task',
                     'lims_task_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'LIMSContactsTableView',
                     'LIMSContactView',
                     'LIMSContactEditView',
                     'LIMSOpportunityView',
                     'LIMSOpportunityEditView',
                     'LIMSTaskView',
                     'LIMSTaskEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createSideBarLink(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if(self::userCanExecute($wgUser)){
            $link = TabUtils::createToolboxLink("LIMS", "$wgServer$wgScriptPath/index.php/Special:LIMS");
            $toolbox['Other']['links'][] = $link;
        }
        return true;
    }

}

?>
