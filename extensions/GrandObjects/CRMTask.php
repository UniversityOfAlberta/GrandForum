<?php

/**
 * @package GrandObjects
 */

class CRMTask extends BackboneModel {

    var $id;
    var $opportunity;
    var $description;
    var $dueDate;
    var $transactions;
    var $status;
	
	static function newFromId($id){
	    $data = DBFunctions::select(array('grand_crm_task'),
	                                array('*'),
	                                array('id' => $id));
	    $opportunity = new CRMOpportunity($data);
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
		    $this->description = $data[0]['description'];
		    $this->dueDate = $data[0]['due_date'];
		    $this->transactions = json_decode($data[0]['transactions']);
		    $this->status = $data[0]['status'];
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getOpportunity(){
	    return $this->opportunity;
	}
	
	function getDescription(){
	    return $this->description;
	}
	
	function getDueDate(){
	    return $this->dueDate;
	}
	
	function getTransactions(){
	    return $this->transactions;
	}
	
	function getStatus(){
	    return $this->status;
	}
	
	function toArray(){
	    $json = array('id' => $this->getId(),
	                  'opportunity' => $this->getOpportunity(),
	                  'description' => $this->getDescription(),
	                  'dueDate' => $this->getDueDate(),
	                  'transactions' => $this->getTransactions(),
	                  'status' => $this->getStatus());
	    return $json;
	}
	
	function create(){
	    DBFunctions::insert('grand_crm_opportunity',
	                        array('opportunity' => $this->opportunity,
	                              'description' => $this->description,
	                              'due_date' => $this->dueDate,
	                              'transactions' => json_encode($this->transactions),
	                              'status' => $this->status));
	    $this->id = DBFunctions::insertId();
	}
	
	function update(){
	    DBFunctions::update('grand_crm_opportunity',
	                        array('opportunity' => $this->opportunity,
	                              'description' => $this->description,
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
