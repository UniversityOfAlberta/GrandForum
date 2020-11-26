<?php

/**
 * @package GrandObjects
 */

class CRMTask extends BackboneModel {

    var $id;
    var $opportunity;
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
	        $tasks[] = new CRMTask(array($row));
	    }
	    return $tasks;
	}
	
	function CRMTask($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->opportunity = $data[0]['opportunity'];
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
	    $json = array('id' => $this->getId(),
	                  'opportunity' => $this->getOpportunity()->getId(),
	                  'task' => $this->getTask(),
	                  'dueDate' => $this->getDueDate(),
	                  'transactions' => $this->getTransactions(),
	                  'status' => $this->getStatus());
	    return $json;
	}
	
	function create(){
	    DBFunctions::insert('grand_crm_opportunity',
	                        array('opportunity' => $this->opportunity,
	                              'task' => $this->task,
	                              'due_date' => $this->dueDate,
	                              'transactions' => json_encode($this->transactions),
	                              'status' => $this->status));
	    $this->id = DBFunctions::insertId();
	}
	
	function update(){
	    DBFunctions::update('grand_crm_opportunity',
	                        array('opportunity' => $this->opportunity,
	                              'task' => $this->task,
	                              'due_date' => $this->dueDate,
	                              'transactions' => json_encode($this->transactions),
	                              'status' => $this->status),
	                        array('id' => $this->id));
	}
	
	function delete(){
	    
	}
	
	function exists(){
        return ($this->getId() > 0);
	}
	
	function getCacheId(){
	    global $wgSitename;
	}
}
?>
