<?php
    require_once( "commandLine.inc" );
    
    class Scraper{

	var $csvData = array();
	var $csvPersonData = array();
	var $csvPubs = array();
	var $csvGrants = array();
	var $csvCourses = array();
	var $csvAdditionalData = array();
	var $courseEvals = array();

	function setCsvData($data){
	    $this->csvPersonData = $data['faculty_staff_member'];
	    $this->csvData = $data;
	    $this->csvPubs = $data['publications'];
	    $this->csvGrants = $data['grants'];
	    $this->csvCourses = $data ['courses'];	    
	    $this->csvAdditionalData['reduced teaching reasons']= $data['reduced teaching reasons'];
            $this->csvAdditionalData['teaching_developments'] = $data['teaching_developments'];
            $this->csvAdditionalData['other_teachings'] = $data['other_teachings'];
            $this->csvAdditionalData['supplementary_professional_activities'] = $data['supplementary_professional_activities.csv'];
            $this->csvAdditionalData['community_outreach_committees'] = $data['community_outreach_committees'];
            $this->csvAdditionalData['departmental_committees'] = $data['departmental_committees'];
            $this->csvAdditionalData['faculty_committees'] = $data['faculty_committees'];
            $this->csvAdditionalData['other_committees'] = $data['other_committees'];
            $this->csvAdditionalData['scientific_committees'] = $data['scientific_committees'];
            $this->csvAdditionalData['university_committees'] = $data['university_committees'];
            $this->csvAdditionalData['additional_data'] = $data['additional_data'];
	}

        function deleteCsvTrailingCommas($data){
            return trim(preg_replace("/(.*?)((,|\s)*)$/m", "$1", $data));
        }
    
        function addUserWebsite($name, $website){
            $_POST['user_name'] = $name;
    	    $_POST['website'] = $website;
    	    APIRequest::doAction('UserWebsite', true);
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
        function createUser($email,$fullname, $title, $department, $university, $universityId){
	    global $wgUser;
	    $role = "Student";
	    $wgUser=User::newFromName("Admin");
	    $nameArray = explode(",",$fullname);
	    $firstname = $nameArray[count($nameArray)-1];
	    $nameArray = explode(" ", $fullname);
	    $lastname = str_replace(",", "", $nameArray[0]);

	    $username = str_replace(" ", "", str_replace("'", "", "$firstname.$lastname"));
	    User::createNew($username, array('real_name' => "$firstname $lastname",
					 'password' => User::crypt(mt_rand()),
					 'email' => $email
					 ));
	     //resetting cache?
	    Person::$cache = array();
	    Person::$namesCache = array();
	    Person::$idsCache = array();
	    Person::$rolesCache = array();
	      //adding user info     
	    addUserUniversity($username, $university, $department, $title);
	    addUserRole($username, $role);
	    $student = Person::newFromNameLike("$firstname $lastname");
	    $student->setUniversityId($universityId);
	    return $student;
        }

        //finds user returns false if student doesn't exist
        function findUser($email, $fullname){
            //add check to see if email exists
            $nameArray = explode(",",$fullname);
	
	    if(count($nameArray)>1){
	        $firstname = $nameArray[count($nameArray)-1];
                $nameArray = explode(" ", $fullname);
                $lastname = str_replace(",", "", $nameArray[0]);
     	        $fullname = "$firstname $lastname";
	    }

	    if($email != ""){
                $person = Person::newFromEmail($email);
	        if($person != null){
	            $id = $person->id; 
                    if($id != 0){
		        return $person;
	            }
                }
	    }
	   //check if fullname works
            $person = Person::newFromNameLike($fullname);
	    $id = $person->id;
            if($id != 0){
	        return $person;
	    }
            return false;
        }

        function formatDate($date){
            $date_array = explode(" ", $date);
	    print_r($date_array);
            if(count($date_array)==2){
                $date = parseSemDate($date_array);
            }
            elseif(count($date_array)==1){
	        $date = str_replace("/","-",$date)."-01 00:00:00";
            }
            else{
                $date = date('Y-m-d', strtotime($date));
                $date = "$date 00:00:00";
            }
	    return $date;
        }
    
        function parseSemDate($date){
            switch ($date[0]){
                case 'Fall':
                    $date = "{$date[1]}-09-01 00:00:00";
                    break;
                case 'Winter':
                    $date = "{$date[1]}-01-01 00:00:00";
                    break;
                case 'Spring':
                    $date = "{$date[1]}-04-01 00:00:00";
                    break;
                case 'Summer':
                    $date = "{$date[1]}-06-01 00:00:00";
                    break;
            }
	    return $date;
        }

        function addRelation($supervisor, $student, $type, $startDate, $endDate){
            if($type == "Supervisor"){
	        $type = "Supervises";
	    }
            $start_date = formatDate($startDate);
            $end_date = formatDate($endDate);
	    DBFunctions::begin();
	    $status = DBFunctions::insert('grand_relations',
			             array('user1' => $supervisor->id,
					   'user2' => $student->id,
					   'type' => $type,
					   'start_date' => $start_date,
					   'end_date' => $end_date),
				     true);
	    if($status){
	        DBFunctions::commit();
	        return $status;
	    }
	    return false;
        }


        function addAwards($student, $title){
            DBFunctions::begin();
            $status = DBFunctions::insert('grand_products',
                                     array('category' => 'Awards',
                                           'type' => "Misc: ".$title,
                                           'title' =>$student->getRealName(),
					   'authors'=>serialize(array($student->id)),
                                           'date' => '2015-01-01 00:00:00'),
                                     true);
            if($status){
                DBFunctions::commit();
                return $status;
            }
            return false;
        }


        function addMovedOn($student, $status, $effective_date){
            DBFunctions::begin();
	    $effective_date = formatDate($effective_date);
	    if($status == 'Yes'){
	         $status = 'Graduated';
	    }
	    $status = DBFunctions::insert('grand_movedOn',
                                     array('user_id' => $student->id,
                                           'status' => $status,
                                           'effective_date' => $effective_date),
                                     true);
            if($status){
                DBFunctions::commit();
                return $status;
            }
            return false;
        }

        function addResearchArea($student, $researchArea, $startdate){
	    DBFunctions::begin();
	    $start_date = formatDate($startdate);
            $disc = $student->getDiscipline();
            $university_info = $student->university;
	      //should do a check to see if exists
            $status = DBFunctions::update('grand_user_university', 
                                    array('research_area' => $researchArea,
                                          'start_date' => $startdate),
                                    array('user_id' => EQ($student->id)));
	    if($status){
	        DBFunctions::commit();
	        return $status;
	    }
	    return false;
        }
 
        function addGrantInfo($title, $pi, $users, $description, $keywords, $scope, $start_date, $end_date){
            DBFunctions::begin();
            $status = DBFunctions::insert('grand_contributions',
                                     array('name' => $title,
                                           'pi' => $pi,
                                           'users' => $users,
                                           'description'=> $description,
                                           'keywords'=> $keywords,
					   'scope' => $scope,
					   'start_date' => $start_date,
					   'end_date'=> $end_date),
                                    true);
            if($status){
                DBFunctions::commit();
                return $status;
            }
            return false; 
	}

        function addActivities($description, $category){
	//TODO
        }

        function addCourses($name){
	//TODO
        }

	function addCourseEvals($course, $evals){

	}
	
	function saveCsv(){




	}

        function enterData($data, $person, $flag=false){
         //first add user's students
        //$person = Person::newFromWgUser();
	    $student = Person::newFromUniversityId($data['id']);
	    //find student
            if(!isset($data['email'])){
                $data['email'] = "";
            }
	    if($student ==""){
	        $student = findUser($data['email'], $data['name']);
            }
	      //if cant find student create a user
	    if($student == false){    
	         $disc = $person->getDiscipline();
                 $university_info = $person->university;
                 $student = createUser($data['email'], $data['name'], $data['program'], $university_info['department'], $university_info['university'], $data['id']); 
            }
	        //enter supervisors
	    foreach($data['supervisors'] as $super){
                $supervisor = findUser("", $super['name']);
	        if($supervisor == false){
		    continue;}
                $type = $super['type'];
                if($type == "Supervisor" || $type == "Co-Supervisor"){
                    $type = "Supervises";
                }
                if(!($supervisor->relatedTo($student, $type))){
                    $status = addRelation($supervisor, $student, $type, $super['start_date'], $super['end_date']);
                    if(!$status){
                        print_r("couldn't add relation {$student->getRealName()}");
                    }
                }
            }
	    if($flag){
	       //enter awards
	        if($data['awards'] != ""){ 
             	    $status = addAwards($student, $data['awards']);
                    if(!$status){
              	         print_r("couldn't add relation {$student->getRealName()}");
             	    }
	        }
	   	    //enter moved on
	        if($data['graduated'] != "Continuing"){
	     	    $status = addMovedOn($student,$data['graduated'],$data['end_date']);
                    if(!$status){
             	        print_r("couldn't add relation {$student->getRealName()}");
             	    }	
	        }
	        return;
	    }
            if(false && $data['research_area'] != ""){
	        $status = addResearchArea($student, $data['research_area'], $data['start_date']);
	        if(!$status){
		    print_r("couldn't add research are {$student->getRealName()}");
	        }
	    }
	    return;
        }
    }
?>
