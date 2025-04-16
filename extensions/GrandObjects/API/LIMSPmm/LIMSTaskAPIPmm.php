<?php

class LIMSTaskAPIPmm extends RESTAPI {
    
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
            $task->assignees = $this->POST('assignees');
            $task->task = $this->POST('task');
            $task->dueDate = $this->POST('dueDate');
            $task->comments = $this->POST('details');
            $task->statuses = (array)$this->POST('statuses');
            $_POST['comments'] = (array)$this->POST('comments');
            $task->files = $this->POST('files');
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
            $task->assignees = $this->POST('assignees');
            $task->task = $this->POST('task');
            $task->dueDate = $this->POST('dueDate');
            $task->comments = $this->POST('details');
            $task->statuses = (array)$this->POST('statuses');
            $_POST['comments'] = (array)$this->POST('comments');
            $task->files = $this->POST('files');
            $task->update();
            return $task->toJSON();
        }
        else{
            return $task->toJSON();
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
