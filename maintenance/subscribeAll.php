<?php
    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    $people = Person::getAllCandidates();

    $iterationsSoFar = 0;
    foreach($people as $person){
        MailingList::subscribeAll($person);
        show_status(++$iterationsSoFar, count($people));
    }
?>
