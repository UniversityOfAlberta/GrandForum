<?php
	require_once( 'commandLine.inc' );
	
	$people = Person::getAllPeople();
	
	foreach($people as $person){
	    $projects = $person->getProjects();
	    foreach($projects as $project){
	        if($project != null){
	            $status = MailingList::subscribe($project, $person);
	            if($status == true){
	                echo "{$person->getName()} added to {$project->getName()} mailing list\n";
	            }
	            else if($status == false){
	                echo "{$person->getName()} not added to {$project->getName()} mailing list\n";
	            }
	        }
	    }
	}
?>
