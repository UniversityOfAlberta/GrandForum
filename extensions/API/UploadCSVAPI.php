<?php
class UploadCSVAPI extends API{

    var $csvData = array();
    var $csvPersonData = array();
    var $csvPubs = array();
    var $csvGrants = array();
    var $csvCourses = array();
    var $csvAdditionalInfo = array();
    var $csvStudents = array();
    var $csvAwards = array();

    function processParams($params){
        //TODO
    }

    function setCsvData($data){
        $lines = str_replace("\n","     ", $data);
        $regex = "/(.+?) ,,,,,,,,,,,,,,,,,,,,,,,,,/";
	preg_match_all($regex, $lines, $array);
	$sectioned = $array[1];
	$sections = array();
     	foreach($sectioned as $section){
	    $p = explode("     ", $section);
	    $sections[] = $p;
	}
	$Endarray = array();
    	foreach($sections as $section){
	    $array = array();
	    $header = "";
	    for($i=0;$i<count($section);$i++){
	        if($section[$i] == ""){
		    continue;
		}
		else{
		    $row = $this->deleteCsvTrailingCommas($section[$i]);
		}
		$array[] = $row;
	    }
	    $Endarray[] = $array;
	}
	$formattedArray = array();
	foreach($Endarray as $info){
	    $header = "";
	    $key = "";
	    $array = array();
	    for($i=0;$i<count($info);$i++){
	        if($i == 0){
		    $key = $info[$i];
		    continue;
		}
		else if($i == 1){
		    $header = str_getcsv($info[$i]);
		    continue;
		}
		else{
		    $row = array();
		     //having to change some keys to match ImportBibTex
		    if($key == 'publications'){
 		        $xrow = str_getcsv($info[$i]);
                        for($x=0;$x<count($xrow); $x++){
                            switch ($header[$x]){
				case 'refereed':
			            $row['peer_reviewed'] = $xrow[$x];
				     break;
				case 'editors':
				    $row['editor'] = $xrow[$x];
				    break;
				case 'type':
				    switch ($xrow[$x]){
					case 'Conference':
					    $xrow[$x] = 'conference';
					    break;
					case 'Journal':
					    $xrow[$x] = 'article';
					    break;
					case 'BookChapter':
					    $xrow[$x] = 'inbook';
					    break;
					case 'Other':
					    $xrow[$x] = 'misc';
					    break;	
				    }
				    $row['bibtex_type'] = trim($xrow[$x]);
				    break;
				case 'publication_date':
				    $dateArray = explode("-", $xrow[$x]);
                                    if(count($dateArray)>1){
                                        $row['year'] = $dateArray[0];
                                        $row['month'] = $dateArray[1];
                                        $row['day'] = $dateArray[2];
                                    }
                                    else{
                                        $row['year'] = "";
                                        $row['month'] = "";
                                        $row['day'] = "";
                                    }
				    break;
				case 'location':
				    $row['city'] = $xrow[$x];
			        default:
				    $row[$header[$x]] = $xrow[$x];
			    }
                        }
		    }
		    else if($key == 'footnotes'){
			$row = $info[$i];
		    }
		    else{
                        $xrow = str_getcsv($info[$i]);
		        for($x=0;$x<count($xrow); $x++){
			     $row[$header[$x]] = $xrow[$x];
			}
		    }
		}
		$array[] = $row;
	    }
	    $formattedArray[$key] = $array;
	}
	$addedPubs = array();
	$newPubs = array();
    	$publications = $formattedArray['publications'];
        for($i = 0; $i<count($publications); $i++){
	     $pub = $publications[$i];
	     if(in_array($pub['title'], $addedPubs)){
		continue;
	     }
	     $addedPubs[] = $pub['title'];
	     $pub['author'][] = $pub['author_name'];
	     for($n = $i+1; $n<count($publications); $n++){
		$pub2 = $publications[$n];
		if($pub['title'] == $pub2['title']){
		    $pub['author'][] = $pub2['author_name'];
		}
	     }
	     $pub['author'] = implode(" and ",$pub['author']);
	      //unsetting all the rows not needed for the ImportBibtex
	     unset($pub['author_name']);
	     unset($pub['ccid']);
	     unset($pub['department']);
	     unset($pub['author_type']);
	     unset($pub['location']);
	     $newPubs[] = $pub;
        }
	$formattedArray['publications'] = $newPubs;
	$data = $formattedArray;
	$this->csvPersonData = $data['faculty_staff_member'];
	$this->csvData = $data;
	$this->csvPubs = $data['publications'];
	$this->csvGrants = $data['grants'];
	$this->csvCourses = $data ['courses'];
        $this->csvStudents = $data['responsibilities'];         
	$this->csvAdditionalInfo['reduced teaching reasons']= $data['reduced teaching reasons'];
        $this->csvAdditionalInfo['teaching_developments'] = $data['teaching_developments'];
        $this->csvAdditionalInfo['other_teachings'] = $data['other_teachings'];
        $this->csvAdditionalInfo['supplementary_professional_activities'] = $data['supplementary_professional_activities.csv'];
        $this->csvAdditionalInfo['community_outreach_committees'] = $data['community_outreach_committees'];
        $this->csvAdditionalInfo['departmental_committees'] = $data['departmental_committees'];
        $this->csvAdditionalInfo['faculty_committees'] = $data['faculty_committees'];
        $this->csvAdditionalInfo['other_committees'] = $data['other_committees'];
        $this->csvAdditionalInfo['scientific_committees'] = $data['scientific_committees'];
        $this->csvAdditionalInfo['university_committees'] = $data['university_committees'];
        $this->csvAdditionalInfo['additional_data'] = $data['additional_data'];
	$this->csvAwards = $data['awards'];
    }

    function deleteCsvTrailingCommas($data){
        return trim(preg_replace("/(.*?)((,|\s)*)$/m", "$1", $data));
    }
    
    function addUserProfile($name, $profile){
        $_POST['user_name'] = $name;
    	$_POST['profile'] = $profile;
    	$_POST['type'] = 'public';
    	APIRequest::doAction('UserProfile', true);
    	$_POST['type'] = 'private';
    	APIRequest::doAction('UserProfile', true);
    }

    function addUserRole($name, $role){
        Person::$cache = array();
        Person::$namesCache = array();
        $_POST['user'] = $name;
    	$_POST['role'] = $role;
    	APIRequest::doAction('AddRole', true);
    }

    function addUserUniversity($name, $university, $department, $title){
        $_POST['user'] = $name;
    	$_POST['user_name'] = $name;
    	$_POST['university'] = $university;
    	$_POST['department'] = $department;
    	$_POST['title'] = $title;
    	APIRequest::doAction('UserUniversity', true);
    }

         //creating new user and assuming department is same as supervisor ??
    function createUser($fullname, $role, $title, $department, $university){
	$nameArray = explode(".",$fullname);
	$firstname = trim($nameArray[0]);
	$lastname = trim($nameArray[1]);
	$username = str_replace(" ", "", str_replace("'", "", "$firstname.$lastname"));
	User::createNew($username, array('real_name' => "$firstname $lastname",
					 'password' => User::crypt(mt_rand()),
					 'email' => ""
					 ));
	         //resetting cache?
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        Person::$rolesCache = array();
	  //adding user info     
	$this->addUserUniversity($username, $university, $department, $title);
	$this->addUserRole($username, $role);
	$student = Person::newFromNameLike("$firstname $lastname");
	return $student;
    }

    function createAwardInfo($person, $awards){
	foreach($awards as $award){
	    if($award['id'] == 'null'){
		continue;
	    }
	    $year = explode("-", $award['created_at']);
	    $year = $year[0];
            $sql = "SELECT * FROM grand_products 
		    WHERE category LIKE 'Awards'
		    AND type LIKE 'Misc: {$award['name']}'
		    AND title LIKE '{$person->getRealName()}'
		    AND date = '$year-01-01 00:00:00'";
	    $data = DBFunctions::execSQL($sql);
	    if(count($data)>0){
	        DBFunctions::begin();
                $status = DBFunctions::insert('grand_products',
                                     array('category' => 'Awards',
                                           'type' => "Misc: ".$title,
                                           'title' =>$student->getRealName(),
					   'authors'=>serialize(array($student->id)),
                                           'date' => "$year-01-01 00:00:00"),
                                     true);
                if($status){
                    DBFunctions::commit();
                }
	    }
        }
	return "Award";
    }

    function createAdditionalInfo($person,$additionals){
	foreach($additionals as $key=>$additional){
	    if($additional['id'] == 'null'){
		continue;
	    }
	    DBFunctions::begin();
	    $status = DBFunctions::insert('grand_university_activities',
				     array('user_id' => $person->getId(),
					   'description' => $additional['description'],
					   'category' => $key),
					   true);
	    if($status){
		DBFunctions::commit();
	    }
	}
	return "AdditionalAdded";
    }

    function createGrantInfo($person, $grants){
    	foreach($grants as $grant){
   	    $contribution = new Contribution(array());
	    $contribution->name = $grant['program'];
	    $contribution->scope = $grant['grant_scope'];
	    $contribution->access_id = $person->getId();
	    $contribution->start_date = "{$grant['starts']} 00:00:00";
	    $contribution->end_date = "{$grant['ends']} 00:00:00";
	    $partner = new Partner(array());
	    $partner->organization = $grant['agency'];
	    $contribution->partners = array($partner);
	    $id = md5(serialize($partner));
	    $contribution->cash = array("$id"=>$grant['total_amount']);
	    $contribution->kind = array("$id"=>0);
	    $contribution->unknown = array("$id"=>0);
	    $contribution->type = array("$id"=>"cash");
	    $contribution->subtype = array("$id"=>"cash");
	    $piArray = array();
	    $pis = explode("&",$grant['pi']);
	    foreach($pis as $pi){
		$newPi = Person::newFromNameLike($pi);
		if($newPi->getId() != ""){
		    $piArray[] = $newPi->getId();
		} 
		else{
		    $piArray[] = $pi;
		}
	    }
	    $contribution->pi = $piArray;
	    $userArray = array();
	    $users = explode(",", $grant['corecipients']);
	    foreach($users as $user){
		$newUser = Person::newFromNameLike($user);
		if($newUser->getId() != ""){
		    $userArray[] = $newUser->getId();
		}
		else{
		    $userArray[] = $user;
		}
	    }
	    $contribution->people = $userArray;
	    $contribution->create();
	}
	return "HIII";
    }

    function createStudentInfo($person, $students){
	foreach($students as $student){
	    $newStudent = Person::newFromNameLike($student['name']);
	    $university = $person->getUniversity();
	    if($newStudent->getId() == ""){
		$newStudent = $this->createUser($student['name'], "Student", $student['responsibility'], $university['department'], $university['university']);		
	    }
	    if(strtolower($student['role']) == "supervisor" || strtolower($student['role']) == "co-supervisor"){
		$student['role'] = "Supervises";
	    }
	    $student['started'] = "{$student['started']} 00:00:00";
	    if($student['ended'] == "NULL"){
		$student['ended'] = "0000-00-00 00:00:00";
	    }
	    else{
		$student['ended'] = "{$student['ended']} 00:00:00";
	    }
	    $oldRelation = Relationship::newFromUser1User2TypeStartDate($person->getId(), $newStudent->getId(), $student['role'], $student['started']);
	    if($oldRelation->getId() == ""){
		$newRelation = new Relationship(array());
		$newRelation->user1 = $person->getId();
		$newRelation->user2 = $newStudent->getId();
		$newRelation->type = $student['role'];
		$newRelation->startDate = $student['started'];
		$newRelation->endDate = $student['ended']; 
		$newRelation->create();
	    }
	    continue;
	}
	return "MADE IT!";
    }

    function createPublicationsInfo($publications){
        $_POST['bibtex'] = "hi";
	$_POST['fec'] = $publications;
        $response = APIRequest::doAction('importBibTeX', true);
	return count($response['errors']);
    }

    function addActivities($person, $activities){
    	
    }

    function calculateCapEnrl($enrollment, $percentage){
        return round($enrollment/($percentage/100));
    }

    function parseCsvDate($date){
   	switch ($date[0]){
            case 'fall':
                $date = "{$date[1]}-09-01 00:00:00";
                break;
            case 'winter':
                $date = "{$date[1]}-01-01 00:00:00";
                break;
            case 'spring':
                $date = "{$date[1]}-04-01 00:00:00";
                break;
            case 'summer':
                $date = "{$date[1]}-06-01 00:00:00";
                break;
        }
        return $date;
    }

    function getTermDays($date){
	$first_date = new DateTime("1900-01-01 00:00:00");
        $second_date = new DateTime($date);
        $difference = $first_date->diff($second_date)->format("%a");
	return $difference;
    }

    function createCourseInfo($person, $courses){
	foreach($courses as $course){
	    $yearString = explode("-", $course['updated_at']);
	    $term = strtolower($course['term']);
            $year = ($term =='fall') ? $yearString[0]-1 : $yearString[0];
	    $dateString = "$term $year";
	    $dateArray = explode(" ", $dateString);
	    $date = $this->parseCsvDate($dateArray);
	    $startDate = $this->getTermDays($date);
	    $endDate = $startDate + 97;
	    $courseCheck = Course::newFromSubjectCatalogSectStartDateTerm($course['subject'], $course['number'],$course['section'], $startDate, $course['term_number']);
	    if($courseCheck->id != ""){
		$professors = $courseCheck->getProfessors();
		$professorCheck = false;
		foreach($professors as $professor){
		    if($professor->getId() == $person->getId()){
		    	$professorCheck = true;
		    }
		}
		if($professorCheck){
			continue;
		}
		$status = DBFunctions::insert('grand_user_courses',
                                    array('user_id' => $person->getId(),
                                          'course_id' => $courseCheck->id),
                                           true);
		continue;
	    }
        	//if not create courses
	    $newcourse = new Course(array());
	    $newcourse->subject = $course['subject'];
	    $newcourse->catalog = $course['number'];
	    $newcourse->component = $course['component'];
	    $newcourse->sect = $course['section'];
	    $newcourse->term = $course['term_number'];
	    $newcourse->startDate = $startDate;
	    $newcourse->endDate = $endDate;
	    $newcourse->totEnrl = $course['enrollment'];
	    $newcourse->capEnrl = $this->calculateCapEnrl($totEnrl,$course['percentage']);    
	    $newcourse->create();
            $courseCheck = Course::newFromSubjectCatalogSectStartDateTerm($course['subject'], $course['number'],$course['section'], $startDate, $course['term_number']);
            $status = DBFunctions::insert('grand_user_courses',
                                    array('user_id' => $person->getId(),
                                          'course_id' => $courseCheck->id),
                                           true);

	}
	return "Finished";
    }

    function createFecInfo($person, $data){
	if($person->dateOfPhd == "0000-00-00 00:00:00" || $person->dateOfPhd == ""){ $person->dateOfPhd = $data['date_of_phd']." 00:00:00";}
        if($person->dateOfAppointment == "0000-00-00 00:00:00" || $person->dateOfAppointment == ""){ $person->dateOfAppointment = $data['date_of_appointment']." 00:00:00";}
        if($person->dateOfAssistant == "0000-00-00 00:00:00" || $person->dateOfAssistant == ""){ $person->dateOfAssistant = $data['date_assistant']." 00:00:00";}
        if($person->dateOfAssociate == "0000-00-00 00:00:00" || $person->dateOfAssociate == ""){ $person->dateOfAssociate = $data['date_associate']." 00:00:00";}
        if($person->dateOfProfessor == "0000-00-00 00:00:00" || $person->dateOfProfessor == ""){ $person->dateOfProfessor = $data['date_professor']." 00:00:00";}
        if($person->dateOfTenure == "0000-00-00 00:00:00" || $person->dateOfTenure == ""){ $person->dateOfTenure = $data['date_tenure']." 00:00:00";}
        if($person->dateOfRetirement == "0000-00-00 00:00:00" || $person->dateOfRetirement == ""){ $person->dateOfRetirement = $data['date_retirement']." 00:00:00";}
        if($person->dateOfLastDegree == "0000-00-00 00:00:00" || $person->dateOfLastDegree == ""){ $person->dateOfLastDegree = $data['date_last_degree']." 00:00:00";}
        if($person->lastDegree == ""){ $person->lastDegree = $data['last_degree'];}
        if($person->publicationHistoryRefereed == "" || $person->publicationHistoryRefereed == 0){ $person->publicationHistoryRefereed = $data['publication_history_refereed'];}
        if($person->publicationHistoryBooks == "" || $person->publicationHistoryBooks == 0){ $person->publicationHistoryBooks = $data['publication_history_books'];}
        if($person->publicationHistoryPatents == "" || $person->publicationHistoryPatents == 0){ $person->publicationHistoryPatents = $data['publication_history_patents'];}
        if($person->dateFso2 == ""){ $person->dateFso2 = $data['date_fso2'];}
        if($person->dateFso3 == ""){ $person->dateFso3 = $data['date_fso3'];}
        if($person->dateFso4 == ""){ $person->dateFso4 = $data['date_fso4'];}
        $person->updateFecInfo();
	return $data['publication_history_refereed'];
    }

    function doAction($noEcho=false){
        global $wgMessage;
        $me = Person::newFromWgUser();
        if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
            $person = Person::newFromId($_POST['id']);
	    $person->getFecPersonalInfo();
        }
        else{
            $person = $me;
            $person->getFecPersonalInfo();
        }
        $csv = $_FILES['csv'];
	if($csv['type'] == "text/csv" && $csv['size'] > 0){
            $file_contents = file_get_contents($csv['tmp_name']);
	    $this->setCsvData($file_contents);
	    $ec = $this->csvPubs;
	    $error = "";
	    $json = array('created' => array(),
			  'error' => array()); 
	    if(isset($_POST['info'])){
		$hi = $this->createFecInfo($person, $this->csvPersonData[0]);
	    }
	    if(isset($_POST['courses'])){
		$hi = $this->createCourseInfo($person, $this->csvCourses);
	    }
	    if(isset($_POST['funding'])){
		$hi = $this->createGrantInfo($person, $this->csvGrants);
	    }
	    if(isset($_POST['supervises'])){
		$json['supervises'] = $this->createStudentInfo($person, $this->csvStudents);
	    }
	    if(isset($_POST['publications'])){
		$hi = $this->createPublicationsInfo($this->csvPubs);
	    }
            if(isset($_POST['additionals'])){
                $hi = $this->createAdditionalInfo($person,$this->csvAdditionalInfo);
            }
	    if(isset($_POST['awards'])){
		$json['created'][] = $this->createAwardInfo($person, $this->csvAwards);
	    }
	    $obj = json_encode($json);

            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded($obj, "$error");
		    </script>
                </head>
            </html>
EOF;
            exit;
	}
	else{
	    echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "The uploaded file was not in CSV format");
                    </script>
                </head>
            </html>
EOF;
            exit;
	}
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
