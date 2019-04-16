<?php
	require_once('commandLine.inc');
	global $wgUser;
	$wgUser = User::newFromId(1);
	$people = Person::getAllCandidates();
	
	$iterationsSoFar = 0;
	$allLists = MailingList::getAllMailingLists();
	foreach($people as $person){
	    if($person->isRole(INACTIVE)){
	        $lists = MailingList::getPersonLists($person);
	        foreach($lists as $list){
	            if(in_array($list, $allLists)){ // Only unsubscribe from 'Managed' lists
                    MailingList::unsubscribe($list, $person);
                }
	        }
	    }
	    show_status(++$iterationsSoFar, count($people));
	}
?>
