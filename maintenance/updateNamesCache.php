<?php
    require_once('commandLine.inc');
    $wgUser = User::newFromId(1);
    $people = Person::getAllPeople();
    foreach($people as $person){
        $person->updateNamesCache();
    }
?>
