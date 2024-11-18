<?php
	require_once('commandLine.inc');
	global $wgUser;
	$wgUser = User::newFromId(1);
	$people = Person::getAllCandidates();
	
	$iterationsSoFar = 0;
	$allLists = MailingList::getAllMailingLists();
	foreach($people as $person){
	    $lists = MailingList::getPersonLists($person);
	    if($person->isRole(INACTIVE)){
	        // Unsubscribe from all lists if inactive
	        foreach($lists as $list){
	            if(in_array($list, $allLists)){ // Only unsubscribe from 'Managed' lists
                    MailingList::unsubscribe($list, $person);
                }
	        }
	    }
	    else{
	        // Unsubscribe from lists which no longer pass the rules
	        $ruleLists = MailingList::getPersonListsByRules($person);
	        foreach($lists as $list){
	            if(in_array($list, $allLists) && !in_array($list, $ruleLists)){ // Only unsubscribe from 'Managed' lists
	                MailingList::unsubscribe($list, $person);
	            }
	        }
	    }
	    show_status(++$iterationsSoFar, count($people));
	}
?>
