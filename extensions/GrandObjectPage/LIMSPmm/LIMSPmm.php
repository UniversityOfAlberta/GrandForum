<?php


BackbonePage::register('LIMSPmm', 'LIMSPmm', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'LIMSPmm::createSideBarLink';

class LIMSPmm extends BackbonePage {
    
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
                     'LIMSContactsTableViewPmm',
                     'LIMSContactViewPmm',
                     'LIMSContactEditViewPmm',
                     'LIMSOpportunityViewPmm',
                     'LIMSOpportunityEditViewPmm',
                     'LIMSTaskViewPmm',
                     'LIMSTaskEditViewPmm');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createSideBarLink(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $link = TabUtils::createToolboxLink("Manage Project", "$wgServer$wgScriptPath/index.php/Special:LIMSPmm");
            $toolbox['Other']['links'][] = $link;
        }
        return true;
    }

}

?>
