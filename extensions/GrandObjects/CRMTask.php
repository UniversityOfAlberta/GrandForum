<?php

/**
 * @package GrandObjects
 */

class CRMTask extends BackboneModel {

    var $id;
    var $opportunity;
    var $assignee;
    var $task;
    var $dueDate;
    var $transactions;
    var $status;
	
	static function newFromId($id){
	    $data = DBFunctions::select(array('grand_crm_task'),
	                                array('*'),
	                                array('id' => $id));
	    $opportunity = new CRMTask($data);
	    return $opportunity;
	}
	
	static function getTasks($opportunity_id){
	    $data = DBFunctions::select(array('grand_crm_task'),
	                                array('*'),
	                                array('opportunity' => $opportunity_id));
	    $tasks = array();
	    foreach($data as $row){
	        $task = new CRMTask(array($row));
	        if($task->isAllowedToView()){
	            $tasks[] = $task;
	        }
	    }
	    return $tasks;
	}
	
	function CRMTask($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->opportunity = $data[0]['opportunity'];
		    $this->assignee = $data[0]['assignee'];
		    $this->task = $data[0]['task'];
		    $this->dueDate = $data[0]['due_date'];
		    $this->transactions = json_decode($data[0]['transactions']);
		    $this->status = $data[0]['status'];
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getOpportunity(){
	    return CRMOpportunity::newFromId($this->opportunity);
	}
	
	function getAssignee(){
	    return $this->assignee;
	}
	
	function getPerson(){
	    return Person::newFromId($this->assignee);
	}
	
	function getTask(){
	    return $this->task;
	}
	
	function getDueDate(){
	    return substr($this->dueDate, 0, 10);
	}
	
	function getTransactions(){
	    return $this->transactions;
	}
	
	function getStatus(){
	    return $this->status;
	}
	
	function isAllowedToEdit(){
        return $this->getOpportunity()->isAllowedToEdit();
    }
    
    function isAllowedToView(){
        return $this->getOpportunity()->isAllowedToView();
    }
    
    static function isAllowedToCreate(){
        return CRMOpportunity::isAllowedToCreate();
    }
	
	function toArray(){
	    if($this->isAllowedToView()){
	        $person = $this->getPerson();
	        $assignee = array('id' => $person->getId(),
	                          'name' => $person->getNameForForms(),
	                          'url' => $person->getUrl());
	                       
	        $json = array('id' => $this->getId(),
	                      'opportunity' => $this->getOpportunity()->getId(),
	                      'assignee' => $assignee,
	                      'task' => $this->getTask(),
	                      'dueDate' => $this->getDueDate(),
	                      'transactions' => $this->getTransactions(),
	                      'status' => $this->getStatus());
	        return $json;
	    }
	    return array();
	}
	
	function create(){
	    if(self::isAllowedToCreate()){
	        DBFunctions::insert('grand_crm_task',
	                            array('opportunity' => $this->opportunity,
	                                  'assignee' => $this->assignee,
	                                  'task' => $this->task,
	                                  'due_date' => $this->dueDate,
	                                  'transactions' => json_encode($this->transactions),
	                                  'status' => $this->status));
	        $this->id = DBFunctions::insertId();
	    }
	}
	
	function update(){
	    if($this->isAllowedToEdit()){
	        DBFunctions::update('grand_crm_task',
	                            array('opportunity' => $this->opportunity,
	                                  'assignee' => $this->assignee,
	                                  'task' => $this->task,
	                                  'due_date' => $this->dueDate,
	                                  'transactions' => json_encode($this->transactions),
	                                  'status' => $this->status),
	                            array('id' => $this->id));
	    }
	}
	
	function delete(){
	    if($this->isAllowedToEdit()){
	        DBFunctions::delete('grand_crm_task',
	                            array('id' => $this->id));
	        $this->id = "";
	    }
	}
	
	function exists(){
        return ($this->getId() > 0);
	}
	
	function getCacheId(){
	    global $wgSitename;
	}
}
?>
