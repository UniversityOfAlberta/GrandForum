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
	$firstname = $nameArray[0];
	$lastname = $nameArray[count($nameArray)-1];
        $username = str_replace(" ", "", str_replace("'", "", "$firstname.$lastname"));
        User::createNew($username, array('real_name' => "$firstname $lastname",
                                         'password' => User::crypt(mt_rand()),
                                         'email' => $info['email']
                                         ));
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        Person::$rolesCache = array();
        $this->addUserUniversity($username, $university_info['university'], $university_info['department'], $info['program']);
	$student = Person::newFromNameLike("$firstname $lastname");
	$this->addUserRole($student->id, $role);
        $student->setUniversityId($info['id']);
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

    function formatDate($date){
        $date_array = explode(" ", $date);
        if(count($date_array)==2){
            $date = parseSemDate($date_array);
        }
        elseif(count($date_array)==1){
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

    function addRelation($supervisors){
	foreach($supervisors as $supervisor){
            if($supervisor['end_date'] == ""){
                $supervisor['end_date'] = $this->formatDate("00/00");
            }
	    return $supervisor['end_date'];
	    $supervisor = $this->findUser("", $supervisor['name']);
	    if($supervisor == false){
		continue;
	    }
	    $type = $supervisor['type'];
	    if($type == "Supervisor" || $type == "Co-Supervisor"){
		$type = "Supervises";
	    }
	    $start_date = formatDate($supervisor['start_date']);
	    $end_date = formatDate($supervisor['end_date']);
	}
    }

    function enterData($students, $person, $flag=false){
	$names = "";
	$count =1;
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
	    if($count < 5){
		$count++;
		continue;
	    }
	    if(count($info['supervisors']) > 0){
		$hi = $this->addRelation($info['supervisors']);
		return $hi;
	    }
/*
            foreach($info['supervisors'] as $super){
                $supervisor = findUser("", $super['name']);
            	if($supervisor == false){
                    continue;
		}
                $type = $super['type'];
                if($type == "Supervisor" || $type == "Co-Supervisor"){
                    $type = "Supervises";
                }
                $start_date = formatDate($super['start_date']);
                $end_date = formatDate($super['end_date']);
                if(!($supervisor->relatedToDuring($student, $type, $start_date, $end_date))){
                    if($flag){
                        $graduated = $info['graduated'];
                        if($graduated != 'Continuing' && $super['end_date'] = ""){
                            $end_date = "2015-01-01 00:00:00"; //TODO: can't have arbitrary date here.
                            $status = addRelation($supervisor, $student, $type, $super['start_date'], $end_date);
                        }
                        else{
                            $status = addRelation($supervisor, $student, $type, $super['start_date'], $super['end_date']);
                        }
                        if(!$status){
                            print_r("couldn't add relation {$student->getRealName()}");
                        }
                    }
                }
	    }
            if($flag){
               //enter awards
                $status = true;
                if($info['awards'] != ""){
                    $awards = $student->getPapers("Awards", false, 'both', true, "Public");
                    foreach($awards as $award){
                        //TODO: ACTUALLY CHECK DATE HERE!
                        $type = "Misc: ".$info['awards'];
                        if($award->type == $type && $award->date == "2015-01-01"){
                            $status = false;
                            break;
                        }
                    }
                    if($status){
                        //$status = addAwards($student, $info['awards']);
                        if(!$status){
                             print_r("couldn't add relation {$student->getRealName()}");
                        }    
                    }
                }
                //enter moved on
                if($info['graduated'] != "Continuing"){
                    if($info['end_date'] == ""){
                        $end_date = "2015-01-01 00:00:00";
                        $status = addMovedOn($student, $info['graduated'], $end_date);
                    }
                    else{
                        $status = addMovedOn($student,$info['graduated'],$info['end_date']);
                    }
                    if(!$status){
                        print_r("couldn't add relation {$student->getRealName()}");
                    }
                }
            return;
            }
            if(false && $info['research_area'] != ""){
                //$status = addResearchArea($student, $info['research_area'], $info['start_date']);
                if(!$status){
                    print_r("couldn't add research are {$student->getRealName()}");
                }
            }
        }*/
   $names = $names.",".$info['name'];
        }
	return $names;                                      
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
	return "parsed";
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
	    $msg = $_POST['password'];
            //$content  = file_get_contents("https://graddb.cs.ualberta.ca/Prod/FECrep.cgi?oracle.login={$_POST['login']}&oracle.password={$_POST['password']}&button=View%20Report");
	    $content = file_get_contents("http://grand.cs.ualberta.ca/~ruby/index.html");
	    $msg = $this->setGradDbInfo($person, $content);
	    $msg = $this->enterData($this->generalInfo, $person, $true);
	    echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "$msg");

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
