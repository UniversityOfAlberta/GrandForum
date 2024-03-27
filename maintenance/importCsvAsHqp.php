<?php

require_once( 'commandLine.inc' );

$wgUser = User::newFromId(1);

if (@$argv[0] == null) {
	echo("No file specified.\n");
	echo("Usage: php importCsvAsHqp.php /path/to/file/file.csv\n");
	echo("       php importCsvAsHqp.php /path/to/files/*\n");
	exit;
}

foreach($argv as $arg) {
	// Check if $arg is a csv
	preg_match('/\.[a-zA-Z]{3}$/', $arg, $matches);
	if ($matches[0] != ".csv") {
		echo("  " . $arg . " is not a csv file, skipping.\n");
		continue;
	}
	importCSV($arg);
}

function importCSV($file) {
	$fileContents = file_get_contents($file);
	$lines = explode("\n", $fileContents);
	$pathChunks = explode("/" , $file);
	$fname = $pathChunks[count($pathChunks)-1];
	$fname = str_replace(".csv", "", $fname);

	$supervisor = Person::newFromName($fname);
	if ($supervisor->getName() == null) {
		echo("  " . $fname . " doesn't match any user, skipping.\n");
		return;
	}
	echo("  importing " . $file . "\n");
	DBFunctions::delete('grand_relations', array(
		'user1'=>$supervisor->getId(),
		'type'=>'Supervises')
	);

	DBFunctions::delete('grand_relations', array(
		'user1'=>$supervisor->getId(),
		'type'=>'Co-Supervises')
	);

	$nLines = count($lines)-1;

	$lineNum = 0;
	foreach($lines as $line) {
		$lineNum++;
		if ($lineNum == 1) {
			continue;
		}
		if ($line == "") {
		    show_status($lineNum, $nLines);
			continue;
		}

		$elems = str_getcsv($line);
		$hqpId 			 = trim($elems[0]);
		$hqpLastName 	 = trim($elems[1]);
		$hqpFirstName    = trim($elems[2]);
		$hqpMiddleName   = trim($elems[3]);
		$hqpEmployeeId   = trim($elems[4]);
		$hqpEmail 		 = trim($elems[5]);
		$hqpRelationship = trim($elems[6]);
		$hqpStatus       = trim($elems[7]);
		$hqpStart 		 = trim($elems[8]);
		$hqpEnd 		 = trim($elems[9]);
		$hqpPosition 	 = trim($elems[10]); // ignored for now
		$delete 		 =@trim($elems[11]);
		$comments		 =@trim($elems[12]);

		if (strstr(strtolower($delete), "yes")) {
		    show_status($lineNum, $nLines);
			continue;
		}
		
		if ($hqpId == "") {
			$username = str_replace(" ", "", preg_replace("/\(.*\)/", "", 
				trim(str_replace(".", "", $hqpFirstName), " -\t\n\r\0\x0B").".".
				trim(str_replace(".", "", $hqpLastName),  " -\t\n\r\0\x0B")
			));
			$username = str_replace("'", "", $username);
			$username = preg_replace("/\".*\"/", "", $username);
			$hqpUser = User::createNew($username, array('password' => User::crypt(mt_rand())));

			$hqp = null;
			if($hqpUser == null){
				$hqp = Person::newFromName($username);
				if ($hqp->getId() == 0) {
					echo($username . " was skipped\n");
					continue;
				}
			}
			if ($hqp == null) {
				$hqp = new LimitedPerson(array());
				$hqp->id = $hqpUser->getId();
				$hqp->name = $hqpUser->getName();
				Person::$namesCache[$hqp->getName()] = $hqp;
				Person::$idsCache[$hqp->getId()] = $hqp;
				//Person::$employeeIdsCache[$row['uid']] = $hqp;
				Person::$cache[strtolower($hqp->getName())] = $hqp;
				Person::$cache[$hqp->getId()] = $hqp;
				//Person::$cache['eId'.$row['uid']] = $hqp; 
			}

		} else {
			$hqp = Person::newFromId($hqpId);
		}

		$hqp->firstName  = $hqpFirstName;
		$hqp->lastName   = $hqpLastName;
		$hqp->middleName = $hqpMiddleName;

		$hqpRealName = $hqpFirstName . " ";
		if ($hqpMiddleName != "") {
			$hqpRealName .= $hqpMiddleName . " ";
		}
		$hqpRealName .= $hqpLastName;
		$hqp->realname = $hqpRealName;
		$hqp->employeeId = $hqpEmployeeId;
		$hqp->email = $hqpEmail;

		$unis = $hqp->getUniversitiesDuring($hqpStart, $hqpEnd);
		$university = null;
		foreach($unis as $uni) {
			if ($uni['position'] == $hqpPosition) {
				$university  = $uni;
				break;
			}
		}

		if ($university == null) {
			addUserUniversity($hqp, "University of Alberta", $supervisor->getDepartment(),
			$hqpPosition, $hqpStart, $hqpEnd);
		}

		$rel = new Relationship(array());
		$rel->user1     = $supervisor->getId();
		$rel->user2     = $hqp->getId();
		$rel->type      = $hqpRelationship;
		$rel->status    = $hqpStatus;
		$rel->startDate = $hqpStart;
		$rel->endDate   = $hqpEnd;
		$rel->comments  = $comments;
		$rel->create();

		$hqp->update();
		DBFunctions::update('mw_user', 
			array('user_email' => $hqpEmail), 
			array('user_id' => $hqp->id));

		show_status($lineNum, $nLines);
	}
}

function addUserUniversity($person, $university, $department, $title, $startDate="", $endDate="0000-00-00 00:00:00"){
	$_POST['university'] = $university;
	$_POST['department'] = $department;
	$_POST['startDate'] = $startDate;
	$_POST['endDate'] = $endDate;
	$_POST['researchArea'] = "";
	$_POST['position'] = $title;
	$api = new PersonUniversitiesAPI();
	$api->params['id'] = $person->getId();
	$api->doPOST();
}
