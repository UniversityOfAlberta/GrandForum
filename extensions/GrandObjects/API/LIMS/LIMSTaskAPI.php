<?php

class LIMSTaskAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $task = LIMSTaskPmm::newFromId($this->getParam('id'));
            return $task->toJSON();
        }
        else{
            $opportunity = LIMSOpportunityPmm::newFromId($this->getParam('opportunity_id'));
            $tasks = new Collection($opportunity->getTasks());
            return $tasks->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(LIMSTaskPmm::isAllowedToCreate()){
            $task = new LIMSTaskPmm(array());
            $task->opportunity = $this->POST('opportunity');
            $task->assignee = $this->POST('assignee')->id;
            $task->task = $this->POST('task');
            $task->dueDate = $this->POST('dueDate');
            $task->comments = $this->POST('comments');
            $task->status = $this->POST('status');
            $task->create();
            return $task->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Task");
        }
    }
    
    function doPUT(){
        $task = LIMSTaskPmm::newFromId($this->getParam('id'));
        if($task->isAllowedToEdit()){
            $task->assignee = $this->POST('assignee')->id;
            $task->task = $this->POST('task');
            $task->dueDate = $this->POST('dueDate');
            $task->comments = $this->POST('comments');
            $task->status = $this->POST('status');
            $task->update();
            return $task->toJSON();
        }
        else{
            $this->throwError("You are not allowed to edit this Task");
        }
    }
    
    function doDELETE(){
        $task = LIMSTaskPmm::newFromId($this->getParam('id'));
        if($task->isAllowedToEdit()){
            $task->delete();
            return $task->toJSON();
        }
        else{
            $this->throwError("You are not allowed to delete this Task");
        }
    }
	
}

?>
