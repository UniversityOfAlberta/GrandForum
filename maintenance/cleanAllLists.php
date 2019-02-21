<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

if(TESTING){
    // Only ever do this if the testing DB is being used
    $people = Person::getAllPeopleDuring(null, "0000-00-00 00:00:00", "9999-12-31 00:00:00");
    foreach($people as $person){
        $lists = MailingList::getPersonLists($person);
        foreach($lists as $list){
            echo "USUB {$person->getName()} <{$person->getEmail()}>: {$list}\n";
            MailingList::unsubscribe($list, $person);
        }
    }
}

?>
