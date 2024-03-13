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
    var $priority;
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
		    $this->priority = $data[0]['priority'];
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
	
	function getPriority(){
	    return $this->priority;
	}
	
	function getStatus(){
	    return $this->status;
	}
	
	function isAllowedToEdit(){
        return ($this->getOpportunity()->isAllowedToEdit() || $this->getPerson()->isMe());
    }
    
    function isAllowedToView(){
        return $this->getOpportunity()->isAllowedToView();
    }
    
    static function isAllowedToCreate(){
        return CRMOpportunity::isAllowedToCreate();
    }
    
    /**
     * Sends an email to the assignee
     * @param Person $assignee The Person to send the email to
     * @param string $type The type of message to send (one of 'new', 'assignee', 'due_date', 'reminder')
     */
    function sendMail($assignee, $type){
        global $config, $wgScriptPath, $wgAdditionalMailParams;
        if($wgScriptPath != ""){
            // Don't send any mail if in a test environment
            return;
        }
        if($assignee == null){
            // This shouldn't be null, but just incase fail silently
            return;
        }
        $message = "";
        $title = "";
        $url = $this->getOpportunity()->getContact()->getUrl();
        switch($type){
            case 'new':
                $title = "{$config->getValue('networkName')} CRM: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (new)";
                $message = "<p>A new CRM task has been assigned to you entitled <a href='{$url}'>{$this->getTask()}</a> with a due date of {$this->getDueDate()}.</p>";
                break;
            case 'assignee':
                $title = "{$config->getValue('networkName')} CRM: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (assigned)";
                $message = "<p>A CRM task has been assigned to you entitled <a href='{$url}'>{$this->getTask()}</a> with a due date of {$this->getDueDate()}.</p>";
                break;
            case 'due_date':
                $title = "{$config->getValue('networkName')} CRM: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (changed)";
                $message = "<p>The CRM task <a href='{$url}'>{$this->getTask()}</a> now has a due date of {$this->getDueDate()}.</p>";
                break;
            case 'reminder':
                $title = "{$config->getValue('networkName')} CRM: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (reminder)";
                $message = "<p>This is a reminder that the CRM task <a href='{$url}'>{$this->getTask()}</a> is due tomorrow.</p>";
                break;
        }
        if($assignee->getEmail() != "" && $title != "" && $message != ""){
            $headers  = "Content-type: text/html\r\n"; 
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($assignee->getEmail(), $title, $message, $headers, $wgAdditionalMailParams);
        }
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
	                      'priority' => $this->getPriority(),
	                      'status' => $this->getStatus(),
	                      'isAllowedToEdit' => $this->isAllowedToEdit());
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
	                                  'priority' => $this->priority,
	                                  'status' => $this->status));
	        $this->id = DBFunctions::insertId();
	        // Send mail to assignee
	        $assignee = Person::newFromId($this->assignee);
	        $this->sendMail($assignee, 'new');
	    }
	}
	
	function update(){
	    if($this->isAllowedToEdit()){
	        $data = DBFunctions::select(array('grand_crm_task'),
	                                    array('*'),
	                                    array('id' => $this->id));
	        if(@$data[0]['assignee'] != $this->assignee){
	            // If the assignee was changed, send email to new assignee
	            $assignee = Person::newFromId($this->assignee);
	            $this->sendMail($assignee, 'assignee');
	        }
	        else if(@substr($data[0]['due_date'],0,10) != $this->getDueDate()){
	            // If Date was changed, send another email to the assignee
	            $assignee = Person::newFromId($this->assignee);
	            $this->sendMail($assignee, 'due_date');
	        }
	        DBFunctions::update('grand_crm_task',
	                            array('opportunity' => $this->opportunity,
	                                  'assignee' => $this->assignee,
	                                  'task' => $this->task,
	                                  'due_date' => $this->dueDate,
	                                  'transactions' => json_encode($this->transactions),
	                                  'priority' => $this->priority,
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
