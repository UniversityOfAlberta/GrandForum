<?php

class MailingListRuleAPI extends RESTAPI {
    
    function doGET(){
        $listId = $this->getParam('listId');
        $ruleId = $this->getParam('ruleId');
        if($ruleId != ""){
            $rule = MailingListRule::newFromId($ruleId);
            return $rule->toJSON();
        }
        else if($listId != ""){
            $list = MailingList::newFromId($listId);
            $rules = $list->getRules();
            $collection = new Collection($rules);
            return $collection->toJSON();
        }
    }
    
    function doPOST(){
        $rule = new MailingListRule(array());
        $rule->value = $this->POST('value');
        $rule->type = $this->POST('type');
        $rule->listId = $this->POST('listId');
        $status = $rule->create();
        if(!$status){
            $this->throwError("There was an error creating the rule");
        }
        return $rule->toJSON();
    }
    
    function doPUT(){
        $ruleId = $this->getParam('ruleId');
        if($ruleId != ""){
            $rule = MailingListRule::newFromId($ruleId);
            $rule->value = $this->POST('value');
            $rule->type = $this->POST('type');
            $rule->listId = $this->POST('listId');
            $status = $rule->update();
            if(!$status){
                $this->throwError("There was an error updating the rule");
            }
            return $rule->toJSON();
        }
        return $this->doGet();
    }
    
    function doDELETE(){
        $ruleId = $this->getParam('ruleId');
        if($ruleId != ""){
            $rule = MailingListRule::newFromId($ruleId);
            $status = $rule->delete();
            if(!$status){
                $this->throwError("There was an error deleting the rule");
            }
            return $rule->toJSON();
        }
        return $this->doGet();
    }
	
}

?>
