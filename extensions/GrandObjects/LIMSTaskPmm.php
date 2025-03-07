<?php

/**
 * @package GrandObjects
 */

class LIMSTaskPmm extends BackboneModel
{

    var $id;
    var $opportunity;
    var $assignee;
    var $task;
    var $dueDate;
    var $comments;
    var $status;

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

    function __construct($data)
    {
        if (count($data) > 0) {
            $this->id = $data[0]['id'];
            $this->opportunity = $data[0]['opportunity'];
            $this->assignee = $data[0]['assignee'];
            $this->task = $data[0]['task'];
            $this->dueDate = $data[0]['due_date'];
            $this->comments = $data[0]['comments'];
            $this->status = $data[0]['status'];
        }
    }

    function getId()
    {
        return $this->id;
    }

    function getOpportunity()
    {
        return LIMSOpportunityPmm::newFromId($this->opportunity);
    }

    function getAssignee()
    {
        return $this->assignee;
    }

    function getPerson()
    {
        return Person::newFromId($this->assignee);
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

    function getStatus()
    {
        return $this->status;
    }

    function isAllowedToEdit()
    {
        return ($this->getOpportunity()->isAllowedToEdit() || $this->getPerson()->isMe());
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
    function sendMail($assignee, $type)
    {
        global $config, $wgScriptPath, $wgAdditionalMailParams;
        if ($wgScriptPath != "") {
            // Don't send any mail if in a test environment
            return;
        }
        if ($assignee == null) {
            // This shouldn't be null, but just incase fail silently
            return;
        }
        $message = "";
        $title = "";
        $url = $this->getOpportunity()->getContact()->getUrl();
        switch ($type) {
            case 'new':
                $title = "{$config->getValue('networkName')} LIMS: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (new)";
                $message = "<p>A new LIMS task has been assigned to you entitled <a href='{$url}'>{$this->getTask()}</a> with a due date of {$this->getDueDate()}.</p>";
                break;
            case 'assignee':
                $title = "{$config->getValue('networkName')} LIMS: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (assigned)";
                $message = "<p>A LIMS task has been assigned to you entitled <a href='{$url}'>{$this->getTask()}</a> with a due date of {$this->getDueDate()}.</p>";
                break;
            case 'due_date':
                $title = "{$config->getValue('networkName')} LIMS: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (changed)";
                $message = "<p>The LIMS task <a href='{$url}'>{$this->getTask()}</a> now has a due date of {$this->getDueDate()}.</p>";
                break;
            case 'reminder':
                $title = "{$config->getValue('networkName')} LIMS: {$this->getOpportunity()->getContact()->getTitle()}, {$this->getDueDate()} (reminder)";
                $message = "<p>This is a reminder that the LIMS task <a href='{$url}'>{$this->getTask()}</a> is due tomorrow.</p>";
                break;
        }
        if ($assignee->getEmail() != "" && $title != "" && $message != "") {
            $headers = "Content-type: text/html\r\n";
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($assignee->getEmail(), $title, $message, $headers, $wgAdditionalMailParams);
        }
    }

    function toArray()
    {
        if ($this->isAllowedToView()) {
            $person = $this->getPerson();
            $assignee = array(
                'id' => $person->getId(),
                'name' => $person->getNameForForms(),
                'url' => $person->getUrl()
            );

            $json = array(
                'id' => $this->getId(),
                'opportunity' => $this->getOpportunity()->getId(),
                'assignee' => $assignee,
                'task' => $this->getTask(),
                'dueDate' => $this->getDueDate(),
                'comments' => $this->getComments(),
                'status' => $this->getStatus(),
                'isAllowedToEdit' => $this->isAllowedToEdit()
            );
            return $json;
        }
        return array();
    }

    function create()
    {
        if (self::isAllowedToCreate()) {
            DBFunctions::insert(
                'grand_pmm_task',
                array(
                    'opportunity' => $this->opportunity,
                    'assignee' => $this->assignee,
                    'task' => $this->task,
                    'due_date' => $this->dueDate,
                    'comments' => $this->comments,
                    'status' => $this->status
                )
            );
            $this->id = DBFunctions::insertId();
            // Send mail to assignee
            $assignee = Person::newFromId($this->assignee);
            $this->sendMail($assignee, 'new');
        }
    }

    function update()
    {
        $me = Person::newFromWgUser();
        if ($this->isAllowedToEdit()) {
            $data = DBFunctions::select(
                array('grand_pmm_task'),
                array('*'),
                array('id' => $this->id)
            );
            $assignee = Person::newFromId($this->assignee);

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
                    'assignee' => $this->assignee,
                    'task' => $this->task,
                    'due_date' => $this->dueDate,
                    'comments' => $this->comments,
                    'status' => $this->status
                ),
                array('id' => $this->id)
            );
            if ($assignee != null && $assignee->getId() != 0) {
                if ($data[0]['status'] == 'Done' && $this->status == 'Closed') {
                    Notification::addNotification($me, $assignee, "Thank You for Completing <b>{$this->task}</b>!",
                    "Hello <b>{$assignee->getNameForForms()}</b>, thank you for completing <b>{$this->task}</b> on I-CONNECTS.
                    We truly appreciate your effort and timely contribution.
                    Your Impact:
                    Your work helps us maintain momentum and reach our goals in collaborative, open team science.
                    The insights or data you provided will guide the next steps for our project and benefit fellow team members.", $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management", false);
                } else {
                    Notification::addNotification($me, $assignee, "Task Updated", "The task <b>{$this->task}</b> has been updated", $this->getOpportunity()->getContact()->getProject()->getUrl() . "?tab=activity-management", false);
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