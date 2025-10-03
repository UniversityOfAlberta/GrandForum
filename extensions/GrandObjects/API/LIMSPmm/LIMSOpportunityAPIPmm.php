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
                    close();
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
    
    function doPOST() {
        $me = Person::newFromWgUser();
        if (LIMSOpportunityPmm::isAllowedToCreate()) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $action = isset($data['action']) ? $data['action'] : null;
            
            if ($action == 'send_notification') {
                $filters = isset($data['filters']) ? $data['filters'] : [];
                $emailContent = isset($data['emailContent']) ? $data['emailContent'] : '';
                return $this->sendEmailNotifications($filters, $emailContent);
            }

            $opportunity = new LIMSOpportunityPmm([]);
            $opportunity->contact = isset($data['contact']) ? $data['contact'] : null;
            $opportunity->owner = isset($data['owner']['id']) ? $data['owner']['id'] : null;
            $opportunity->description = isset($data['description']) ? $data['description'] : '';
            $opportunity->files = isset($data['files']) ? $data['files'] : [];
            $opportunity->create();
            return $opportunity->toJSON();
        } else {
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
    private function sendEmailNotifications($filters, $emailContent) {        
        if (empty($emailContent)) {
            $this->throwError("Email content cannot be empty");
        }
        if (empty($filters)) {
            $this->throwError("Please select at least one filter");
        }
        
        $opportunityId = $this->getParam('id');
        $opportunity = LIMSOpportunityPmm::newFromId($opportunityId);
        $allTasks = $opportunity->getTasks();
        $me = Person::newFromWgUser();
        $project = $opportunity->getContact()->getProject();
        $allMembers = $project->getAllPeople();

        $filteredTasks = $allTasks;
        foreach ($filters as $filterType => $filterValue) {
            if (empty($filterValue)) {
                continue;
            }

            $tasksThatMatch = [];

            foreach ($filteredTasks as $task) {
                switch ($filterType) {
                    case 'taskName':
                        if ($task->getTask() == $filterValue) {
                            $tasksThatMatch[] = $task;
                        }
                        break;

                    case 'taskType':
                        if ($task->getTaskType() == $filterValue) {
                            $tasksThatMatch[] = $task;
                        }
                        break;

                    case 'assigneeStatus':
                        $statuses = $task->getStatuses();
                        $assignees = $task->getAssignees();
                        foreach ($assignees as $assignee) {
                            $assigneeId = $assignee->getId();
                            $currentStatus = isset($statuses[$assigneeId]) ? $statuses[$assigneeId] : "Assigned";
                            if ($currentStatus == $filterValue) {
                                $tasksThatMatch[] = $task;
                                break;
                            }
                        }
                        break;
                }
            }
            $filteredTasks = $tasksThatMatch;
        }

        $assigneesToNotify = [];
        foreach ($filteredTasks as $task) {
            $assignees = $task->getAssignees();
            foreach ($assignees as $assignee) {
                if ($assignee->getId() == -1) {
                    foreach ($allMembers as $member) {
                        $assigneesToNotify[$member->getId()] = $member;
                    }
                } else {
                    $assigneesToNotify[$assignee->getId()] = $assignee;
                }
            }
        }

        $projectLeaders = $project->getLeaders();
        foreach ($projectLeaders as $leader) {
            $assigneesToNotify[$leader->getId()] = $leader;
        }

        foreach ($assigneesToNotify as $assignee) {
            Notification::addNotification(
                $me, 
                $assignee, 
                "Email Notification", 
                $emailContent, 
                $opportunity->getContact()->getProject()->getUrl() . "?tab=activity-management", 
                true
            );
        }

        $totalRecipients = count($assigneesToNotify);
        $leaderCount = count($projectLeaders);

        $messageCount = max(0, $totalRecipients - $leaderCount);
        
        return json_encode([
            'status' => 'success', 
            'message' => 'Email notification sent to ' . $messageCount . ' assignees'
        ]);
    }
}

?>
