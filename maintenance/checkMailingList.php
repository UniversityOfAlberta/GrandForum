<?php
	require_once( 'commandLine.inc' );
	$blackList = array();
	if(file_exists("mailingListBlackList.txt")){
	    $blackList = explode("\n", file_get_contents("mailingListBlackList.txt"));
	}
	
	function isBlackListed($email){
	    global $blackList;
	    return !(array_search(trim($email), $blackList) === false);
	}
	
	$type = $argv[0];
	if($type == "HQP"){
	    $mailman = "grand-forum-hqps";
	    $people = array_merge(Person::getAllPeople(HQP));
	}
	else if ($type == "STUD"){
	    $mailman = "hqp-students";
	    $tmppeople = Person::getAllPeople(HQP);
	    $people = array();
	    foreach($tmppeople as $person){
	        $title = $person->getPosition();
	        if($title == "Undergraduate" ||
	           $title == "Masters Student" ||
	           $title == "PhD Student" ||
	           $title == "PostDoc"){
	            $people[] = $person;
	        }
	    }
	}
	else if($type == "RMC"){
	    $mailman = "rmc-list";
	    $people = array_merge(Person::getAllPeople(RMC));
	}
	else if($type == "ISAC"){
	    $mailman = "isac-list";
	    $people = array_merge(Person::getAllPeople(ISAC));
	}
	else if($type == "NI"){
	    $mailman = "grand-forum-researchers";
	    $people = array_merge(Person::getAllPeople(CNI), 
	                          Person::getAllPeople(PNI),
	                          Person::getAllPeople(AR));
	    
	}
	else if($type == "PL"){
	    $people = array();
	    $mailman = "grand-forum-project-leaders";
	    $peeps = Person::getAllPeople('all');
	    foreach($peeps as $p){
	        if($p->isProjectLeader() || $p->isProjectCoLeader()){
	            $add = false;
	            foreach($p->leadership() as $project){
	                if($project->isSubProject()){
	                    continue;
	                }
	                $add = true;
	            }
	            if($add){
	                $people[] = $p;
	            }
	        }
	    }
	}
	else if($type == "PL1"){
	    $people = array();
	    $mailman = "grand-forum-p1-leaders";
	    $peeps = Person::getAllPeople('all');
	    foreach($peeps as $p){
	        if($p->isProjectLeader() || $p->isProjectCoLeader()){
	            $add = false;
	            foreach($p->leadership() as $project){
	                if($project->isSubProject() || $project->getPhase() != 1){
	                    continue;
	                }
	                $add = true;
	            }
	            if($add){
	                $people[] = $p;
	            }
	        }
	    }
	}
	else if($type == "PL2"){
	    $people = array();
	    $mailman = "grand-forum-p2-leaders";
	    $peeps = Person::getAllPeople('all');
	    foreach($peeps as $p){
	        if($p->isProjectLeader() || $p->isProjectCoLeader()){
	            $add = false;
	            foreach($p->leadership() as $project){
	                if($project->isSubProject() || $project->getPhase() != 2){
	                    continue;
	                }
	                $add = true;
	            }
	            if($add){
	                $people[] = $p;
	            }
	        }
	    }
	}
	else if($type == "CHAMP2"){
	    $people = array();
	    $mailman = "grand-forum-p2-champions";
	    $peeps = Person::getAllPeople(CHAMP);
	    $projects = Project::getAllProjects();
	    foreach($peeps as $p){
	        foreach($projects as $project){
	            if($project->getPhase() == 2){
	                if($p->isChampionOfDuring($project, REPORTING_CYCLE_START, REPORTING_RMC_MEETING)){
                        $people[$p->getId()] = $p;
                        break;
	                }
	            }
	            foreach($project->getSubProjects() as $sub){
	                if($p->isChampionOfDuring($sub, REPORTING_CYCLE_START, REPORTING_RMC_MEETING)){
                        $people[$p->getId()] = $p;
                        break;
	                }
	            }
	        }
	    }
	}
	else if($type == "LOCATION"){
	    $people = array_merge(Person::getAllPeople(PNI),
	                          Person::getAllPeople(CNI),
	                          Person::getAllPeople(AR));
	    $array = array();
	    foreach($people as $person){
	        $uni = $person->getUni();
	        foreach(MailingList::getListByUniversity($uni) as $list){
	            if(!MailingList::isSubscribed($list, $person) && !isBlackListed($person->getEmail()) && !MailingList::hasUnsubbed($list, $person)){
	                $array[$list][] = "{$person->getName()} - {$person->getEmail()}\n";
	            }
	        }
	    }
	    foreach($array as $list => $people){
	        echo "== Active People not on $list ==\n";
	        foreach($people as $person){
	            echo $person;
	        }
	        echo "\n";
	    }
	    exit;
	}
	
    exec("/usr/lib/mailman/bin/list_members $mailman", $list);
    $nMissing = 0;
    $nExtra = 0;

	echo "== Active $type not on $mailman ==\n";
	foreach($people as $person){
	    if(!MailingList::isSubscribed($mailman, $person) && !isBlackListed($person->getEmail()) && !MailingList::hasUnsubbed($mailman, $person)){
	        echo $person->getEmail()."\n";
	        $nMissing++;
	    }
	}
	/*echo "\n== Inactive $type on $mailman == \n";
	foreach($list as $email){
	    $found = false;
	    foreach($people as $person){
	        if(trim(strtolower($person->getEmail())) == trim(strtolower($email))){
	            $found = true;
	            break;
	        }
	    }
	    if(!$found){
	        echo $email."\n";
	        $nExtra++;
	    }
	}*/
	echo "\n";
	/*
	echo "\n== Totals == \n";
	echo "  Missing: $nMissing\n";
	echo "    Extra: $nExtra\n";
	echo "\n";*/
?>
