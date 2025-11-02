<?php

class LIMSSendNotifications extends RESTAPI {

    function doPOST() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $projectId = isset($data['projectId']) ? $data['projectId'] : null;
        if (!$projectId) {
            $this->throwError("Project ID is required");
        }
        $filters = isset($data['filters']) ? $data['filters'] : [];
        $emailContent = isset($data['emailContent']) ? $data['emailContent'] : '';
        $emailSubject = isset($data['emailSubject']) ? $data['emailSubject'] : '';

        if (empty($emailContent)) {
            $this->throwError("Email content cannot be empty");
        }
        if (empty($filters)) {
            $this->throwError("Please select at least one filter");
        }
        
        $project = Project::newFromId($projectId);
        $allTasks = $project->getTasks();
        $allMembers = $project->getAllPeople();
        $me = Person::newFromWgUser();

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

        foreach ($assigneesToNotify as $assignee) {
            Notification::addNotification(
                $me, 
                $assignee, 
                "Email Notification: " . $emailSubject, 
                $emailContent, 
                $project->getUrl() . "?tab=activities", 
                true
            );
        }

        $projectName = $project->getName(); 
        $taskNameFilter = $filters['taskName'] ?? null;
        
        $contentPrefix = "Project: <b>{$projectName}</b><br><br>";
        if ($taskNameFilter) {
            $contentPrefix .= "Task Name: <b>{$taskNameFilter}</b><br><br>";
        }
        $finalEmailContent = $contentPrefix . "---<br><br>" . $emailContent;

        $projectLeaders = $project->getLeaders();
        foreach ($projectLeaders as $leader) {
            Notification::addNotification(
                $me, 
                $leader, 
                "Email Notification: " . $emailSubject, 
                $finalEmailContent, 
                $project->getUrl() . "?tab=activities", 
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

    function doPUT() {
        $this->throwError("PUT method not supported for this endpoint.", 405);
    }

    function doDELETE() {
        $this->throwError("DELETE method not supported for this endpoint.", 405);
    }
 
    function doGET(){
        $this->throwError("POST method not supported for this endpoint.", 405);
    }
}