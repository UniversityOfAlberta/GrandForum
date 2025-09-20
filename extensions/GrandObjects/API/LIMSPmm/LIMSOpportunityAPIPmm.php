<?php

class LIMSOpportunityAPIPmm extends RESTAPI {
    
    function doGET(){
        $files = ($this->getParam('files') != "");
        $file_id = $this->getParam('file_id');
        if($this->getParam('id') != ""){
            $opportunity = LIMSOpportunityPmm::newFromId($this->getParam('id'));
            if($files && $file_id != ""){
                $file = $opportunity->getFile($file_id);
                if(isset($file['data']) && isset($file['type']) && isset($file['filename'])){
                    header('Content-Type: '.$file['type']);
                    header('Content-Disposition: attachment; filename="'.$file['filename'].'"');
                    $exploded = explode("base64,", $file['data']);
                    echo base64_decode(@$exploded[1]);
                    exit;
                }
            }
            return $opportunity->toJSON();
        }
        else{
            $contact = LIMSContactPmm::newFromId($this->getParam('contact_id'));
            $opportunities = new Collection($contact->getOpportunities());
            return $opportunities->toJSON();
        }
    }
    
    function doPOST(){
        $input = file_get_contents('php://input');
        $jsonData = null;

        if (!empty($input)) {
            $jsonData = json_decode($input, true);
        }

        $action = null;
        if ($jsonData && isset($jsonData['action'])) {
            $action = $jsonData['action'];
            foreach ($jsonData as $key => $value) {
                $_POST[$key] = $value;
            }
        } else {
            $action = $this->POST('action');
        }
        
        if ($action == 'send_notification') {
            return $this->sendEmailNotifications();
        }
        $me = Person::newFromWgUser();
        if(LIMSOpportunityPmm::isAllowedToCreate()){
            $opportunity = new LIMSOpportunityPmm(array());
            $opportunity->contact = $this->POST('contact');
            $opportunity->owner = $this->POST('owner')->id;
            $opportunity->description = $this->POST('description');
            $opportunity->files = $this->POST('files');
            $opportunity->create();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Opportunity");
        }
    }
    
    function doPUT(){
        $opportunity = LIMSOpportunityPmm::newFromId($this->getParam('id'));
        if($opportunity->isAllowedToEdit()){
            $opportunity->owner = $this->POST('owner')->id;
            $opportunity->description = $this->POST('description');
            $opportunity->files = $this->POST('files');
            $opportunity->update();
            return $opportunity->toJSON();
        }
        else{
            return $opportunity->toJSON();

        }
    }
    
    function doDELETE(){
        $opportunity = LIMSOpportunityPmm::newFromId($this->getParam('id'));
        if($opportunity->isAllowedToEdit()){
            $opportunity->delete();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to delete this Opportunity");
        }
    }
    private function sendEmailNotifications() {
        $filterType = $this->POST('filterType');
        $filterValue = $this->POST('filterValue');
        $emailContent = $this->POST('emailContent');
        $assigneesToNotify = [];
        
        if (empty($filterType) || empty($filterValue) || empty($emailContent)) {
            $this->throwError("Missing required fields for email notification");
        }
        
        $opportunityId = $this->getParam('id');
        $opportunity = LIMSOpportunityPmm::newFromId($opportunityId);
        $tasks = $opportunity->getTasks();
        $me = Person::newFromWgUser();
        $project = $opportunity->getContact()->getProject();
        $allMembers = $project->getAllPeople();

        if ($filterType == "Task Name") {
            foreach($tasks as $task) {
                if($task->getTask() == $filterValue) {
                    $assignees = $task->getAssignees();
                    foreach($assignees as $assignee) {
                        if($assignee->getId() == -1) {
                            foreach($allMembers as $member) {
                                $assigneesToNotify[] = $member;
                            }
                        } else {
                            $assigneesToNotify[] = $assignee;
                        }
                    }
                }
            }
        } else if($filterType == "Task Type") {
            foreach ($tasks as $task) {
                if ($task->getTaskType() == $filterValue) {
                    $assignees = $task->getAssignees();
                    foreach($assignees as $assignee) {
                        if($assignee->getId() == -1) {
                            foreach($allMembers as $member) {
                                $assigneesToNotify[] = $member;
                            }
                        } else {
                            $assigneesToNotify[] = $assignee;
                        }
                    }
                }
            }
        } else if($filterType == "Assignee Status"){
            foreach($tasks as $task) {
                $assignees = $task->getAssignees();
                $statuses = $task->getStatuses();
                
                foreach($assignees as $assignee) {
                    $assigneeId = $assignee->getId();
                    $currentStatus = isset($statuses[$assigneeId]) ? $statuses[$assigneeId] : "Assigned";
                    
                    if($currentStatus == $filterValue) {
                        if($assigneeId == -1) {
                            foreach($allMembers as $member) {
                                $assigneesToNotify[] = $member;
                            }
                        } else {
                            $assigneesToNotify[] = $assignee;
                        }
                    }
                }
            }
        }
        
        $uniqueAssignees = [];
        foreach($assigneesToNotify as $assignee) {
            $uniqueAssignees[$assignee->getId()] = $assignee;
        }

        $assigneesToNotify = array_values($uniqueAssignees);
        
        foreach($assigneesToNotify as $assignee) {
            Notification::addNotification($me, $assignee, "Email Notification", $emailContent, 
                $opportunity->getContact()->getProject()->getUrl() . "?tab=activity-management", true);
        }
        
        return json_encode(array('status' => 'success', 'message' => 'Email notification sent to ' . count($assigneesToNotify) . ' assignees'));
    }
}

?>
