<?php

use Google\Service\AccessContextManager\Status;
use Google\Service\StreetViewPublish\Place;

/**
 * @package GrandObjects
 */

class LIMSTaskPmm extends BackboneModel
{

    var $id;
    var $opportunity;
    var $assignees;
    var $task;
    var $dueDate;
    var $comments;
    var $statuses;
    var $files;

    static function newFromId($id)
    {
        $data = DBFunctions::select(
            array('grand_pmm_task'),
            array('*'),
            array('id' => $id)
        );
        $opportunity = new LIMSTaskPmm($data);
        return $opportunity;
    }

    static function getTasks($opportunity_id)
    {
        $data = DBFunctions::select(
            array('grand_pmm_task'),
            array('*'),
            array('opportunity' => $opportunity_id)
        );
        $tasks = array();
        foreach ($data as $row) {
            $task = new LIMSTaskPmm(array($row));
            if ($task->isAllowedToView()) {
                $tasks[] = $task;
            }
        }
        return $tasks;
    }

   
     function getAssignees()
    {
        $data = DBFunctions::select(
            array('grand_pmm_task_assginees'),
            array('*'),
            array('task_id' => $this->id)
        );
        $assignees = array();
        foreach ($data as $row) {
            $assignee = Person::newFromId($row['assignee']);
            $assignees[] = $assignee;
        }
        return $assignees;
    }

   

    function __construct($data)
    {
        global $wgServer, $wgScriptPath;
        if (count($data) > 0) {
            $this->id = $data[0]['id'];
            $this->opportunity = $data[0]['opportunity'];
            // $this->assignee = $data[0]['assignee'];
            $this->task = $data[0]['task'];
            $this->dueDate = $data[0]['due_date'];
            $this->comments = $data[0]['comments'];
            $files = DBFunctions::select(array('grand_pmm_task_assginees'),
                                         array('id', 'filename', 'type'),
                                         array('task_id' => $this->id));
            foreach($files as $file){
                $file['data'] = '';
                $file['url'] = "{$wgServer}{$wgScriptPath}/index.php?action=api.limstaskpmm/{$this->id}/files/{$file['id']}";
                $this->files[] = $file;
            }
        // $this->status = $data[0]['status'];
        }
    }

    function getId()
    {
        return $this->id;
    }

    function getFiles(){
        return $this->files;
    }
    function getFile($id){
        if($this->isAllowedToView()){
            $file = DBFunctions::select(array('grand_pmm_task_assginees'),
                                        array('*'),
                                        array('id' => $id,
                                              'task_id' => $this->id));
            return @$file[0];
        }
        return "";
    }

    function getOpportunity()
    {
        return LIMSOpportunityPmm::newFromId($this->opportunity);
    }

    function getPerson(){
        return $this->getOpportunity()->getPerson();
    }

    function isMember()
    {
        $me = Person::newFromWgUser();
        $assignees = $this->getAssignees();
        $personId = $me->getId();
        foreach($assignees as $assignee) {
            if ($assignee->getId() == $personId) {
                return true;
            }
        }

        return false;
    }

    function getTask()
    {
        return $this->task;
    }

    function getDueDate()
    {
        return substr($this->dueDate, 0, 10);
    }

    function getComments()
    {
        return $this->comments;
    }

    function getStatuses()
    {
        $data = DBFunctions::select(
            array('grand_pmm_task_assginees'),
            array('*'),
            array('task_id' => $this->id)
        );
        $statuses = array();
        foreach ($data as $row) {
            $statuses[$row['assignee']] = $row['status'];
        }
        return $statuses;
    }

    function isAllowedToEdit()
    {
        
        return ($this->getOpportunity()->isAllowedToEdit() || $this->isMember() );
    }

    function isAllowedToView()
    {
        return $this->getOpportunity()->isAllowedToView();
    }

    static function isAllowedToCreate()
    {
        return LIMSOpportunityPmm::isAllowedToCreate();
    }

    /**
     * Sends an email to the assignee
     * @param Person $assignee The Person to send the email to
     * @param string $type The type of message to send (one of 'new', 'assignee', 'due_date', 'reminder')
     */
    
    function toArray()
    {
        if ($this->isAllowedToView()) {
            $people = $this->getAssignees();
            $assignees = array();
            foreach($people as $person){
                $assignees[] = array(
                    'id' => $person->getId(),
                    'name' => $person->getNameForForms(),
                    'url' => $person->getUrl()
                );
            }

            $json = array(
                'id' => $this->getId(),
                'opportunity' => $this->getOpportunity()->getId(),
                'assignees' => $assignees,
                'task' => $this->getTask(),
                'dueDate' => $this->getDueDate(),
                'details' => $this->getComments(),
                'statuses' => $this->getStatuses(),
                'isAllowedToEdit' => $this->isAllowedToEdit(),
                'files' => $this->getFiles()
            );
            return $json;
        }
        return array();
    }

    function create()
    {
        $me = Person::newFromWgUser();
        if (self::isAllowedToCreate()) 
        {
            DBFunctions::insert(
                'grand_pmm_task',
                array(
                    'opportunity' => $this->opportunity,
                    // 'assignee' => $this->assignee,
                    'task' => $this->task,
                    'due_date' => $this->dueDate,
                    'comments' => $this->comments,
                    // 'status' => $this->status
                )
            );
            $this->id = DBFunctions::insertId();
            foreach($this->assignees as $assignee){
                $assigneeId = (isset($assignee->id)) ? $assignee->id : $assignee;

                DBFunctions::insert(
                    'grand_pmm_task_assginees',
                    array(
                        'task_id' => $this->id,
                        'assignee' => $assigneeId,
                        'status' => @$this->statuses[$assigneeId]
                    )
                );
            }
            $this->uploadFiles();

           
            // Send mail to assignee
            // $assignee = Person::newFromId($this->assignee);
            // Notification::addNotification($me, $assignee, "Task Created", "The task <b>{$this->task}</b> has been created", $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management", false);

            // Assume $assignees is an array of assignee objects (or IDs, depending on how you store them)
            $assignees = $this->getAssignees();
            foreach ($assignees as $assignee) {
                $comment = @$_POST['comments'][$assignee->id];

                // If assignee is an object, you can get their email like this:
                // (Note: Adjust this based on how you retrieve the email or other relevant information)
                
                // Create the notification for each assignee
                Notification::addNotification(
                    $me, 
                    $assignee, 
                    "Task Created", 
                    "The task <b>{$this->task}</b> has been created. Comments: <b>{$comment}</b>", 
                    $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management", 
                    true
                );
            }

        }
    }

    function uploadFiles(){
        
        foreach($this->files as $assigneeId => $file){
            if(isset($file->data) && $file->data != ''){
                DBFunctions::update('grand_pmm_task_assginees',
                                    array('filename' => $file->filename,
                                          'type' => $file->type,
                                          'data' => $file->data),
                                    array('task_id' => $this->id,
                                        'assignee'=>$assigneeId));
            }
            else if(isset($file->delete) && $file->delete == true){
                DBFunctions::update('grand_pmm_task_assginees',
                                    array('filename' => NULL,
                                          'type' => NULL,
                                          'data' => NULL),
                                    array('task_id' => $this->id,
                                    'assignee'=>$assigneeId));
            }
        }
    }

    function update()
    {
        $me = Person::newFromWgUser();
        if ($this->isAllowedToEdit()) {
            $data = array();
            foreach(DBFunctions::select(
                array('grand_pmm_task'=>'t', 'grand_pmm_task_assginees'=>'a'),
                array('*'),
                array('a.task_id' => $this->id, 't.id' => $this->id),
            ) as $row) {
                $data[$row['assignee']] = $row;
            }

            
            // $assignee = Person::newFromId($this->assignee);

            // if(@$data[0]['assignee'] != $this->assignee){
            //     // If the assignee was changed, send email to new assignee
            //     $this->sendMail($assignee, 'assignee');
            // }
            // else if(@substr($data[0]['due_date'],0,10) != $this->getDueDate()){
            //     // If Date was changed, send another email to the assignee
            //     $this->sendMail($assignee, 'due_date');
            // }
            DBFunctions::update(
                'grand_pmm_task',
                array(
                    'opportunity' => $this->opportunity,
                      // 'assignee' => $this->assignee,
                    'task' => $this->task,
                    'due_date' => $this->dueDate,
                    'comments' => $this->comments,
                    // 'status' => $this->status
                ),
                array('id' => $this->id)
            );

            DBFunctions::delete(
                'grand_pmm_task_assginees',
                array('task_id' => $this->id)
            );
            foreach($this->assignees as $assignee){
                $assigneeId = (isset($assignee->id)) ? $assignee->id : $assignee;

                DBFunctions::insert(
                    'grand_pmm_task_assginees',
                    array(
                        'task_id' => $this->id,
                        'assignee' => $assigneeId,
                        'status' => @$this->statuses[$assigneeId]
                    )
                );
            }
            $this->uploadFiles();
            $assignees = $this->getAssignees();
            foreach ($assignees as $assignee) {
      
                
                $comment = @$_POST['comments'][$assignee->id];

                // If assignee is an object, you can get their email like this:
                // (Note: Adjust this based on how you retrieve the email or other relevant information)
                // Create the notification for each assignee
                if ( @$data[$assignee->id]['status'] != 'Closed' && $this->statuses[$assignee->id] == 'Closed') {
                    Notification::addNotification($me, $assignee, "Thank You for Completing <b>{$this->task}</b>!",
                    "Hello <b>{$assignee->getNameForForms()}</b>, thank you for completing <b>{$this->task}</b> on I-CONNECTS.
                    We truly appreciate your effort and timely contribution.
                    Your Impact:
                    Your work helps us maintain momentum and reach our goals in collaborative, open team science.
                    The insights or data you provided will guide the next steps for our project and benefit fellow team members.
                    Comments: <b>{$comment}</b>", $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management", true);
                } else {
                    Notification::addNotification($me, $assignee, "Task Updated", "The task <b>{$this->task}</b> has been updated. Comments: <b>{$comment}</b>", $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management", true);
                }
            }
            // Send email to leader if an assignee left a comment
            $leaders = $this->getOpportunity()->getContact()->getProject()->getLeaders();
            $comment = @$_POST['comments'][$me->getId()];

            foreach ($leaders as $leader) {
                if ($leader->getId() != $me->getId()) {
                    Notification::addNotification(
                        $me,
                        $leader,
                        "New Comment on Task: <b>{$this->task}</b>",
                        "Assignee <b>{$me->getNameForForms()}</b> left a comment on the task <b>{$this->task}</b>:<br><b>{$comment}</b>",
                        $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management",
                        true
                    );
                }
            }
            
            
        }
    }

    function delete()
    {
        if ($this->isAllowedToEdit()) {
            DBFunctions::delete(
                'grand_pmm_task',
                array('id' => $this->id)
            );
            $this->id = "";
        }
    }

    function exists()
    {
        return ($this->getId() > 0);
    }

    function getCacheId()
    {
        global $wgSitename;
    }
}
?>
