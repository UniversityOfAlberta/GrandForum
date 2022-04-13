<?php

$wgHooks['BeforePageDisplay'][] = 'initActionPlan';

function initActionPlan($out, $skin){
    global $wgServer, $wgScriptPath, $config;
    $me = Person::newFromWgUser();
    
    BackbonePage::$dirs['actionplanpage'] = dirname(__FILE__);
    $actionPlan = new ActionPlanPage();
    $actionPlan->loadTemplates();
    $actionPlan->loadModels();
    $actionPlan->loadHelpers();
    $actionPlan->loadViews();
    $actionPlan->loadMain();
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
        return array('Backbone/*',
                     'action_plan',
                     'action_plan_create',
                     'action_plan_tracker');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'ActionPlanView',
                     'ActionPlanCreateView',
                     'ActionPlanTrackerView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
