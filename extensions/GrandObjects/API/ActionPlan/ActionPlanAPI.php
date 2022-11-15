<?php

class ActionPlanAPI extends RESTAPI {

    function doGET(){
        $me = Person::newFromWgUser();
        if($this->getParam('id') != ""){
            $plan = ActionPlan::newFromId($this->getParam('id'));
            if(!$plan->canUserRead()){
                $this->throwError("You are not allowd to view this action plan");
            }
            return $plan->toJSON();
        }
        else{
            $plans = new Collection(ActionPlan::newFromUserId($me->getId()));
            return $plans->toJSON();
        }
    }

    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in to create an Action Plan");
        }
        $plan = new ActionPlan(array());
        $plan->type = $this->POST('type');
        $plan->fitbit = $this->POST('fitbit');
        $plan->goals = $this->POST('goals');
        $plan->barriers = $this->POST('barriers');
        $plan->plan = $this->POST('plan');
        $plan->tracker = $this->POST('tracker');
        $plan->components = $this->POST('components');
        $plan->submitted = $this->POST('submitted');
        $plan->create();
        return $plan->toJSON();
    }

    function doPUT(){
        $plan = ActionPlan::newFromId($this->getParam('id'));
        if(!$plan->canUserRead()){
            $this->throwError("You are not allowed to edit this Action Plan");
        }
        $plan->type = $this->POST('type');
        $plan->fitbit = $this->POST('fitbit');
        $plan->goals = $this->POST('goals');
        $plan->barriers = $this->POST('barriers');
        $plan->plan = $this->POST('plan');
        $plan->tracker = $this->POST('tracker');
        $plan->components = $this->POST('components');
        $plan->submitted = $this->POST('submitted');
        $plan->update();
        return $plan->toJSON();
    }

    function doDELETE(){
        return false;
    }
}

?>
