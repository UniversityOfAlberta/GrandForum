<?php
    require_once('commandLine.inc');
    $wgUser = User::newFromId(1);
    $people = Person::getAllPeople();
    $iterationsSoFar = 0;
    foreach($people as $person){
        $person->updateNamesCache();
        show_status(++$iterationsSoFar, count($people));
    }
?>
