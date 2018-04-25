<?php

class ImportGradDbAPI extends API{
    var $generalInfo = array();
    var $individualInfo = array();
 
    function processParams($params){
    }

    function addUserRole($userId, $role){
        $new_role = new Role(array());
	$new_role->user = $userId;
	$new_role->role = $role;
	$new_role->startDate = date("Y-m-d 00:00:00");
	$new_role->create();
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
    function createUser($info, $university_info){
	$role = "Student";
        $nameArray = explode(" ", $info['name']);
	$firstname = trim($nameArray[0]);
	$lastname = trim($nameArray[count($nameArray)-1]);
        $username = str_replace(" ", "", str_replace("'", "", "$firstname.$lastname"));
	User::createNew($username, array('real_name' => "$firstname $lastname",
                                         'password' => User::crypt(mt_rand()),
                                         'email' => ""
                                         ));
        Person::$cache = array();
        Person::$rolesCache = array();
        $this->addUserUniversity($username, $university_info['university'], $university_info['department'], $info['program']);
        $student = Person::newFromNameLike("$firstname $lastname");
        $this->addUserRole($student->id, $role);
        $student->setUniversityId($info['id']);
        DBFunctions::commit();
        return $student;
    }

    function formatName($fullname){
	$nameArray = explode(",", $fullname);
	if(count($nameArray)>1){
	    $firstname = $nameArray[count($nameArray)-1];
	    $nameArray = explode(" ", $fullname);
	    $lastname = str_replace(",", "", $nameArray[0]);
	    $fullname = trim($firstname)." ".trim($lastname);
	}
	return $fullname;
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

    function formatDate($date){
	$date_array = explode(" ", trim($date));
        if(count($date_array)==2){
            $date = $this->parseSemDate($date_array);
        }
        else if(count($date_array)==1){
	     if($date == "00/00"){
		return "0000-00-00 00:00:00";
	     }
             $date = str_replace("/","-",$date)."-01 00:00:00";
        }
        else{
            $date = date('Y-m-d', strtotime($date));
            $date = "$date 00:00:00";
        }
        return $date;
    }

    //finds user returns false if student doesn't exist
    function findUser($email, $fullname){
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

    function addRelation($supervisors, $student){
	$students = array();
	foreach($supervisors as $supervisor){
            if(!isset($supervisor['end_date']) || $supervisor['end_date'] == ""){
                $supervisor['end_date'] = "00/00";
            }
	    $supervisor_obj = $this->findUser("", $supervisor['name']);
	    if($supervisor_obj == false){
		continue;
	    }
            $type = trim($supervisor['type']);
	    if(strtolower($type) == "supervisor" || strtolower($type) == "co-supervisor"){
		$type = "Supervises";
	    }
            $start_date = $this->formatDate($supervisor['start_date']);
            $end_date = $this->formatDate($supervisor['end_date']);
            //if($graduated != 'Continuing' && $super['end_date'] = ""){
              //  $end_date = date('Y-m-d h:i:s', time()); //TODO: can't have arbitrary date here.
	    //}
            $oldRelation = Relationship::newFromUser1User2TypeStartDate($supervisor_obj->getId(), $student->getId(), $type);
	    
	   if($oldRelation->getId() == ""){
                $newRelation = new Relationship(array());
                $newRelation->user1 = $supervisor_obj->getId();
                $newRelation->user2 = $student->getId();
                $newRelation->type = $type;
                $newRelation->startDate = $start_date;
                $newRelation->endDate = $end_date;
                $status = $newRelation->create();
		if($status){
                    DBFunctions::commit();
		    $students[] = $status;
		}
            }
            continue;
	}
    }

    function addAwards($title, $student){
        $awards = $student->getPapers("Awards", false, 'both', true, "Public");
	$current_date = date('Y-m-d h:i:s', time());
	foreach($awards as $award){
            $type = "Misc: ".$title;
            if($award->type == $type && $award->date == $current_date){
                return;
            }
	}
        DBFunctions::begin();
        $status = DBFunctions::insert('grand_products',
                                     array('category' => 'Awards',
                                           'type' => "Misc: ".$title,
                                           'title' =>$student->getRealName(),
                                           'authors'=>serialize(array($student->id)),
                                           'date' => $current_date),
                                     true);
        if($status){
            DBFunctions::commit();
            return $status;
        }
        return false;
    }

    function addResearchArea($student, $researchArea, $startdate){
        DBFunctions::begin();
        $start_date = $this->formatDate($startdate);
        $disc = $student->getDiscipline();
        $university_info = $student->university;
          //should do a check to see if exists
        $status = DBFunctions::update('grand_user_university',
                                    array('research_area' => $researchArea,
                                          'start_date' => $start_date),
                                    array('user_id' => EQ($student->id)));
        if($status){
            DBFunctions::commit();
            return $status;
        }
        return false;
    }

    function enterData($students, $person, $flag=false){
	$students = array();
	foreach($students as $info){
	    $student = Person::newFromUniversityId($info['id']);
            if(!isset($info['email'])){
                $info['email'] = "";
            }
	    $info['name'] = $this->formatName($info['name']);
            if($student->id == ""){
                 $student = $this->findUser($info['email'], $info['name']);
            }
            if($student == false){
                $disc = $person->getDiscipline();
                $university_info = $person->university;
		$student = $this->createUser($info,$university_info);
            }
	    if(isset($info['supervisors']) && count($info['supervisors']) > 0){
		$students = $this->addRelation($info['supervisors'], $student);
	    }

	    if(isset($info['awards']) && $info['awards'] != ""){
		$this->addAwards($info['awards'], $student);
	    }

            if(isset($info['research_area']) && $info['research_area'] != ""){
                $status = $this->addResearchArea($student, $info['research_area'], $info['start_date']);
            }
        }
	return $students;
    }
	
    function setGradDbInfo($person, $data){
	$url = $data;
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
                    $data[$keys[$i]] = str_replace("<","", trim($info))." Student";
                }
                else{
                     $data[$keys[$i]] = str_replace("<","",trim($info));
                     if($keys[$i] == 'graduated'){
                         $supervisorsArray[] = array('name'=>$person->getRealName(), 'type'=>$data['role'], 'start_date'=>$data['start_date'], 'end_date'=>$data['end_date']);
                         $data['supervisors'] = $supervisorsArray;
                     }
            	}
                $i++;
            }
            $this->generalInfo[] = $data;
        }
    }

    function parse($regex, $url){
        preg_match_all($regex, $url, $Array);
        return $Array[1][0];
    }

    function setGradDbExtraInfo($person, $url){
        $regex = '/option value\=\"(.+?)\"\>/';
        preg_match_all($regex, $url, $Array);
        $students = $Array[1];
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
    	    $data['id'] = trim($this->parse($regex, $url));
	    $regex = '/\<input type\=\"hidden\" name\=\"name\" value\=\"(.+?)\"/';
	    $data['name'] = trim($this->parse($regex, $url));
	    $regex = '/Email\: \<\/th\> \<td\>(.+?)\<\/td\>/';
    	    $data['email'] = trim($this->parse($regex,$url));
	    $regex = '/Program\: \<\/b\>(.+?)\</';
	    $data['program'] = trim($this->parse($regex,$url));
	    $regex = '/Start Date\: \<\/b\>(.+?)\</';
    	    $data['start_date'] = trim($this->parse($regex, $url));
	    $regex = '/Research Area(.+?)\<\/b\>(.+?)\</';
    	    preg_match_all($regex, $url, $Array);
	    $data['research_area'] = trim(str_replace("<br />", "", $Array[2][0]));
    	    $regex = '/Thesis Title\: \<\/b\>(.+?)\</';
    	    $data['thesis'] = trim(str_replace("<br />", "", $this->parse($regex, $url)));
    	    $regex = '/End Date\<\/th\>\<\/tr\>(.+?)\<\/table\>/';
	    $supervisorsArray=array();
	    $table = $this->parse($regex,$url);
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
		    $superdata[$keys[$i]] = trim($info);
		    $i++;
	        }
	        $supervisorsArray[] = $superdata;
    	    }
	    $data['supervisors']=$supervisorsArray;
            $parsed_array[] = $data;
        }
        foreach($parsed_array as $student){
            $this->individualInfo[] = $student;
        }
    }

    function doAction($noEcho=false){
        global $wgMessage;
        $me = Person::newFromWgUser();
        if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
            $person = Person::newFromId($_POST['id']);
        }
        else{
            $person = $me;
        }
	if($_POST['login'] != "" && $_POST['password'] != ""){
            $error = "";
            $json = array('created' => array(),
                          'error' => array());
	    $msg = $_POST['password'];
            $content  = file_get_contents("https://graddb.cs.ualberta.ca/Prod/FECrep.cgi?oracle.login={$_POST['login']}&oracle.password={$_POST['password']}&button=View%20Report");
	    $msg = $this->setGradDbInfo($person, $content);
	    $json['students'] = $this->enterData($this->generalInfo, $person, $true);
	    $extra_content = file_get_contents("https://graddb.cs.ualberta.ca/Prod/login.cgi?oracle.login={$_POST['login']}&oracle.password={$_POST['[password']}");
	    $msg = $this->setGradDbExtraInfo($person, $extra_content);
	    $msg = $this->individualInfo[0]['id'];
            $msg = $this->enterData($this->individualInfo, $person, $true);
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
                                            parent.ccvUploaded([], "Please provide both a username and password.");

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
