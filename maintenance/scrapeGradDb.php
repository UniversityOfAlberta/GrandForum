<?php
        //written to input prof student relations to database
    require_once( "commandLine.inc" );
        //functions and code based on install.php
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

    function parse($regex, $url){
	preg_match_all($regex, $url, $Array);
	return $Array[1][0];
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
             //$student = createUser($data['email'], $data['name'], $data['program'], $university_info['department'], $university_info['university'], $data['id']); 
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
	    $start_date = formatDate($super['start_date']);
            $end_date = formatDate($super['end_date']);
            if(!($supervisor->relatedToDuring($student, $type, $start_date, $end_date))){
		if($flag){
		    $graduated = $data['graduated'];
		    if($graduated != 'Continuing' && $super['end_date'] = ""){
			$end_date = "2015-01-01 00:00:00"; //TODO: can't have arbitrary date here.
			$status = addRelation($supervisor, $student, $type, $super['start_date'], $end_date);
		    }
		    else{
			$status = addRelation($supervisor, $student, $type, $super['start_date'], $super['end_date']);
		    }
		}
		else{
                    $status = addRelation($supervisor, $student, $type, $super['start_date'], $super['end_date']);
		}
		if(!$status){
                    print_r("couldn't add relation {$student->getRealName()}");
                }
            }
        }
	if($flag){
	   //enter awards
	    $status = true;
	    if($data['awards'] != ""){
		$awards = $student->getPapers("Awards", false, 'both', true, "Public");
	        foreach($awards as $award){
			//TODO: ACTUALLY CHECK DATE HERE!
		    $type = "Misc: ".$data['awards'];
		    if($award->type == $type && $award->date == "2015-01-01"){
			$status = false;
			break;
		    }
		}	
		if($status){
             	    //$status = addAwards($student, $data['awards']);
                    if(!$status){
              	         print_r("couldn't add relation {$student->getRealName()}");
             	    }
	        }
	    }
	   	//enter moved on
	     if($data['graduated'] != "Continuing"){
		if($data['end_date'] == ""){
		    $end_date = "2015-01-01 00:00:00";
		    $status = addMovedOn($student, $data['graduated'], $end_date);
		}
		else{
	     	    $status = addMovedOn($student,$data['graduated'],$data['end_date']);
                }
		if(!$status){
             	     print_r("couldn't add relation {$student->getRealName()}");
             	}	
	     
	      }
	return;
	}
        if(false && $data['research_area'] != ""){
	    //$status = addResearchArea($student, $data['research_area'], $data['start_date']);
	    if(!$status){
		print_r("couldn't add research are {$student->getRealName()}");
	    }
	}
    }

//--------------------------------------------MAIN HERE -----------------------------------------------------------------------
   $mf = array();
   $url = file_get_contents('http://grand.cs.ualberta.ca/~ruby/index.html');
    
    $person = Person::newFromNameLike('Eleni Stroulia');

    $regex = '/Graduated\<\/th\>\<\/tr\>\<\/tr\>\<\/tr\>(.+?)\<\/table\>/';
      //only taking table that has info in it
    preg_match_all($regex, $url, $url);
      //take each row
    $regex = '/\<tr\>(.+?)\<\/tr\>/';
    preg_match_all($regex, $url[1][0], $Array);
    
    $rows = $Array[1];
    $students = array();
    foreach($rows as $row){
    	$regex = '/\<td(.+?)\>(.+?)\\/td\>/';
    	preg_match_all($regex, $row, $Array);
        $studentrow = $Array[2];
	$students[] = $studentrow;
    }

    $keys = array('id','name','program','awards','role','start_date','end_date','graduated');
    $parsed_array = array();
    foreach($students as $student){
	$data = array();
	$i = 0;
	$supervisorsArray = array();
	foreach($student as $info){
	    if($keys[$i] == 'program'){
		$data[$keys[$i]] = str_replace("<","", $info)." Student";
	    }
	    else{
	         $data[$keys[$i]] = str_replace("<","",$info);
	         if($keys[$i] == 'graduated'){
                     $supervisorsArray[] = array('name'=>$person->getRealName(), 'type'=>$data['role'], 'start_date'=>$data['start_date'], 'end_date'=>$data['end_date']);
                     $data['supervisors'] = $supervisorsArray;
                 }
	    }
	    $i++;
	}
	
	$parsed_array[] = $data;	
    }
    foreach($parsed_array as $student){
    //    enterData($student,$person, true);
    	$mf[] = $student;
    }
    print_r($mf);
    $person = Person::newFromNameLike('Eleni Stroulia');
    $url = file_get_contents('https://graddb.cs.ualberta.ca/Prod/login.cgi?oracle.login=stroulia&oracle.password=Bella1Alex2');
    print_r(count(explode("\n", $url)));
    $regex = '/option value\=\"(.+?)\"\>/';
    preg_match_all($regex, $url, $Array);
    $students = $Array[1];
    print_r($students);
    $parsed_array = array();
    foreach($students as $student){
	$data = array();
	$site = "https://graddb.cs.ualberta.ca/Prod/login.cgi?";
	$site .= "oracle.login=stroulia";
	$site .= "&oracle.password=Bella1Alex";
	$site .= "2&button=Program%20Summary";
	$site .= "&stulist=$student";
   	$url = file_get_contents($site);
    	  //parsing through html
	$regex ='/Student Id\: \<\/b\>(.+?)\<br/';
    	$data['id'] = parse($regex, $url);
	$regex = '/\<input type\=\"hidden\" name\=\"name\" value\=\"(.+?)\"/';
	$data['name'] = parse($regex, $url);
	$regex = '/Email\: \<\/th\> \<td\>(.+?)\<\/td\>/';
    	$data['email'] = parse($regex,$url);
	$regex = '/Program\: \<\/b\>(.+?)\</';
	$data['program'] = parse($regex,$url);
	$regex = '/Start Date\: \<\/b\>(.+?)\</';
    	$data['start_date'] = parse($regex, $url);
	$regex = '/Research Area(.+?)\<\/b\>(.+?)\</';
    	preg_match_all($regex, $url, $Array);
	$data['research_area'] = str_replace("<br />", "", $Array[2][0]);
    	$regex = '/Thesis Title\: \<\/b\>(.+?)\</';
    	$data['thesis'] = str_replace("<br />", "", parse($regex, $url));
    	$regex = '/End Date\<\/th\>\<\/tr\>(.+?)\<\/table\>/';
	$supervisorsArray=array();
	$table = parse($regex,$url);
    	$regex = '/\<tr\>(.+?)\<\/tr\>/';
   	preg_match_all($regex, $table, $row);
    	$supervisors = $row[1];
	$keys = array('name','type','start_date','end_date');
	foreach($supervisors as $supervisor){
	    $superdata = array();
	    $i = 0;
	    $regex = '/\<td\>(.+?)\<\/td\>/';
            preg_match_all($regex, $supervisor,$infos);
            $infos = $infos[1];
	    foreach($infos as $info){
		$superdata[$keys[$i]] = $info;
		$i++;
	    }
	    $supervisorsArray[] = $superdata;
    	}
	$data['supervisors']=$supervisorsArray;
    $parsed_array[] = $data;
    }
    foreach($parsed_array as $student){
      //  enterData($student,$person, false);
        $mf[] = $student;
    }
    print_r($mf);
?> 
