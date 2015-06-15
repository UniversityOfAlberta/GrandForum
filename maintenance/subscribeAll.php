<?php
	require_once( 'commandLine.inc' );
	global $wgUser;
	$wgUser = User::newFromId(1);
	$people = Person::getAllPeople();
	
	foreach($people as $person){
	    MailingList::subscribeAll($person);
	}
?>
