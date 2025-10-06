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
                    'lims_status_change',
                    'lims_status_check',
                    'lims_comment_history',
                    'lims_email_notification_view',
                    'project_tasks_main',
                    'task_row');
    }
    
    function getViews(){
        return array('Backbone/*',
                    'LIMSStatusChangeViewPmm',
                    'LIMSStatusCheckViewPmm',
                    'LIMSCommentHistoryPmm',
                    'LIMSEmailNotificationViewPmm',
                    'ProjectTaskView',
                    'TaskRowView');
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
