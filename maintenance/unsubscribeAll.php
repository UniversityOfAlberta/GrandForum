<?php
	require_once('commandLine.inc');
	global $wgUser;
	$wgUser = User::newFromId(1);
	$people = Person::getAllCandidates();
	
	$iterationsSoFar = 0;
	foreach($people as $person){
	    if($person->isRole(INACTIVE)){
	        $lists = MailingList::getPersonLists($person);
	        foreach($lists as $list){
                MailingList::unsubscribe($list, $person);
	        }
	    }
	    show_status(++$iterationsSoFar, count($people));
	}
?>
