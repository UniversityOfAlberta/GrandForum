<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

$date = new DateTime('tomorrow');
$tomorrow = $date->format('Y-m-d');

foreach(CRMContact::getAllContacts() as $contact){
    foreach($contact->getOpportunities() as $opportuntity){
        foreach($opportuntity->getTasks() as $task){
            if($tomorrow == $task->getDueDate()){
                $task->sendMail($task->getPerson(), 'reminder');
            }
        }
    }
}


?>
