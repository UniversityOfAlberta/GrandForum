<?php
    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    $people = array_merge(Person::getAllPeople(), Person::getAllCandidates());

    $newPeople = array();
    foreach($people as $person){
        $newPeople[$person->getId()] = $person;
    }
    $people = $newPeople;

    $iterationsSoFar = 0;
    foreach($people as $person){
        MailingList::subscribeAll($person);
        show_status(++$iterationsSoFar, count($people));
    }
?>
