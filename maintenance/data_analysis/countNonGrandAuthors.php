<?php

require_once('../commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}

//sortAuthors();
sortAuthorsWithLikeness();

function sortAuthors(){
	$nongrand = array();

    $papers = Paper::getAllPapers('all', 'all', 'grand');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

      	$authors = $paper->getAuthors();

        foreach($authors as $au){
        	$id = $au->getId();
        	$name = $au->getName();
        	if($id == ""){
        		if(array_key_exists($name, $nongrand)){
        			$nongrand[$name] += 1;
        		}
        		else{
        			$nongrand[$name] = 1;
        		}
        	}
        }    

    }
   arsort($nongrand);
   echo '"Name","Count"'."\n";
   foreach($nongrand as $name => $count){
   	echo '"'.$name.'"'.','.'"'.$count.'"'."\n";
   }
   
}

function sortAuthorsWithLikeness(){
	$nongrand = array();

    $papers = Paper::getAllPapers('all', 'all', 'grand');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

      	$authors = $paper->getAuthors();

        foreach($authors as $au){
        	$id = $au->getId();

        	if($id == ""){
        		$name = $au->getName();
	        	$name = trim($name);
	        	$name_split = preg_split('/ /', $name);
	        	$clean_name = array();
	        	foreach($name_split as $s){
	        		$clean_name[] = trim($s);
	        	}
	        	$clean_name = implode(' ', $clean_name);

        		if(array_key_exists($clean_name, $nongrand)){
        			$nongrand[$clean_name] += 1;
        		}
        		else{
        			$nongrand[$clean_name] = 1;
        		}
        	}
        }    

    }

    echo "Done counting NON-GRAND Authors\n\n";

   	$allPeople = Person::getAllPeople();

	arsort($nongrand);
	//print_r($nongrand);
	echo '"Name","Count","Similar"'."\n";
	foreach($nongrand as $name => $count){
		//if($count > 1){
			echo '"'.$name.'","'.$count.'",';
			$similar = array();
			foreach($allPeople as $person){
				$forum_person_name = $person->getFirstName() ." ".$person->getLastName();
				similar_text($forum_person_name, $name, $percent);
				if($percent > 70){
					$similar[] = $forum_person_name;
				}
			}
			echo '"'. implode("; ", $similar) .'"'."\n";
		//}
	}
   
}
