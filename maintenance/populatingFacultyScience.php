<?php
	//written to input faculty of science professors to database
  require_once( "commandLine.inc" );
	//functions and code based on install.php
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
    print_r($_POST);
    APIRequest::doAction('UserUniversity', true);
  }
$wgUser=User::newFromName("Admin");
	if(file_exists("facultyOfScienceMissed.csv")){
	    print_r("Reading in data");
            $lines = explode("\n", file_get_contents("facultyOfScienceMissed.csv"));
            foreach($lines as $line){
                $cells = str_getcsv($line);
                if(count($cells) > 1){
                    $lname = @trim($cells[0]);
                    $fname = @trim($cells[1]);
                    $role = @trim($cells[2]);
                    $website = @trim($cells[3]);
                    $university = @trim($cells[4]);
                    $department = @trim($cells[5]);
                    $title = @trim($cells[6]);
                    $email = @trim($cells[7]);
                    $profile = @trim($cells[8]);
                    $username = str_replace(" ", "", str_replace("'", "", "$fname.$lname"));
			//add check to see if exist
		    $person = Person::newFromEmail($email);
                    if($person != null){          
                        $id = $person->getId();
		    
                        if($id != 0){
                            continue;
                        }
		    }
		    $fullname=$fname . $lname;
                    $person = Person::newFromName($fullname);
                    $id = $person->getId();
                    if($id != 0){
                      continue;
                    }

                    User::createNew($username, array('real_name' => "$fname $lname",
                                                 'password' => User::crypt(mt_rand()),
                                                 'email' => $email));
                    Person::$cache = array();
                    Person::$namesCache = array();
                    Person::$idsCache = array();
                    Person::$rolesCache = array();
                    addUserUniversity($username, $university, $department, $title);
                    addUserRole($username, $role);
                    addUserWebsite($username, $website);
                    addUserProfile($username, $profile);
        }
    }
    print_r("inserted data");
}
else{
	print_r("error reading file");
}
?>
