<?php

$wgHooks['BeforePageDisplay'][] = 'initActionPlan';

function initActionPlan($out, $skin){
    global $wgServer, $wgScriptPath, $wgOut, $config;
    $me = Person::newFromWgUser();
    
    BackbonePage::$dirs['actionplanpage'] = dirname(__FILE__);
    $actionPlan = new ActionPlanPage();
    $actionPlan->loadTemplates();
    $actionPlan->loadModels();
    $actionPlan->loadHelpers();
    $actionPlan->loadViews();
    $actionPlan->loadMain();
    $wgOut->addScript("<link href='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/ActionPlan/style.css?".filemtime(dirname(__FILE__)."/style.css")."' type='text/css' rel='stylesheet' />");
    return true;
}

class ActionPlanPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
    
    function getTemplates(){
        global $wgOut;
        $wgOut->addHTML("<script type='text/javascript'>
            var actionPlanDate = \"".date('Y-m-d', strtotime('last thursday +4 days'))."\";
        </script>");
        return array('Backbone/*',
                     'action_plan',
                     'action_plan_create',
                     'action_plan_tracker',
                     'action_plan_history');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'ActionPlanView',
                     'ActionPlanCreateView',
                     'ActionPlanTrackerView',
                     'ActionPlanHistoryView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
