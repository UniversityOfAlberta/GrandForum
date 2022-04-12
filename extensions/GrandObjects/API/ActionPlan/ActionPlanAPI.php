<?php

class ActionPlanAPI extends RESTAPI {

    function doGET(){
        $me = Person::newFromWgUser();
        if($this->getParam('id') != ""){
            $plan = ActionPlan::newFromId($this->getParam('id'));
            if(!$plan->canView()){
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
        return $this->doGET();
    }

    function doPUT(){
        return $this->doGET();
    }

    function doDELETE(){
        return false;
    }
}

?>
