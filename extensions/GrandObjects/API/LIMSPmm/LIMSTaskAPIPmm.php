<?php

class LIMSTaskAPIPmm extends RESTAPI {
    
    function doGET(){
        $task_id = $this->getParam('id');
        $file_id = $this->getParam('file_id');

        $is_assignee_file = ($this->getParam('files') != "");
        $is_task_file = ($this->getParam('taskfiles') != "");

        if ($task_id != "" && $file_id != "") {
            $task = LIMSTaskPmm::newFromId($task_id);
            $file = null;

            if ($is_task_file) {
                $file_array = $task->getTaskFile($file_id);
                $file = $file_array[0] ?? null;
            } else if ($is_assignee_file) {
                $file = $task->getFile($file_id);
            }
            if ($file) {
                if (isset($file['data']) && isset($file['type']) && isset($file['filename'])) {
                    header('Content-Type: ' . $file['type']);
                    header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
                    $exploded = explode("base64,", $file['data']);
                    echo base64_decode(@$exploded[1]);
                    
                    close(); 
                } else {
                    $this->throwError("File data is corrupt or incomplete.", 500);
                }
            }
        }

        if ($task_id != "") {
            $task = LIMSTaskPmm::newFromId($task_id);
            return $task->toJSON();
        } 
        else if ($this->getParam('project_id') != "") {
            $project = Project::newFromId($this->getParam('project_id'));
            $tasks = new Collection($project->getTasks());
            return $tasks->toJSON();
        }
        else {
            $this->throwError("Missing required parameter 'id' or 'project_id'", 400);
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(LIMSTaskPmm::isAllowedToCreate()){
            $task = new LIMSTaskPmm(array());
            $task->projectId = $this->POST('projectId');
            $task->assignees = $this->POST('assignees');
            $task->reviewers = $this->POST('reviewers');
            $task->task = $this->POST('task');
            $task->dueDate = $this->POST('dueDate');
            $task->details = $this->POST('details');
            $task->statuses = (array)$this->POST('statuses');
            $task->taskType = $this->POST('taskType');
            $_POST['comments'] = (array)$this->POST('comments');
            $task->files = $this->POST('files');
            $task->commentsHistory = $this->POST('commentsHistory');
            $task->taskFiles = $this->POST('taskFiles');
            $task->newTaskFile = $this->POST('newTaskFile');
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
            $task->projectId = $this->POST('projectId');
            $task->reviewers = $this->POST('reviewers');
            $task->task = $this->POST('task');
            $task->dueDate = $this->POST('dueDate');
            $task->details = $this->POST('details');
            $task->statuses = (array)$this->POST('statuses');
            $_POST['comments'] = (array)$this->POST('comments');
            $task->taskType = $this->POST('taskType');
            $task->files = $this->POST('files');
            $task->commentsHistory = $this->POST('commentsHistory');
            $task->taskFiles = $this->POST('taskFiles');
            $task->newTaskFile = $this->POST('newTaskFile');
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
