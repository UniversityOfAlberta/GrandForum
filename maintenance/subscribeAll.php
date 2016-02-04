<?php
	require_once( 'commandLine.inc' );
	global $wgUser;
	$wgUser = User::newFromId(1);
	$people = array_merge(Person::getAllPeople(), Person::getAllCandidates());
	
	foreach($people as $person){
	    MailingList::subscribeAll($person);
	}
?>
