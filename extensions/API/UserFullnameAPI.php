<?php

class UserFullnameAPI extends API{

    function UserFullnameAPI(){
        $this->addPOST("fname", true, "The first name of the user", "Admin");
        $this->addPOST("mname", true, "The middle name of the user", "Admin");
        $this->addPOST("lname", true, "The last name of the user", "Admin");
    }

    function processParams($params){
    }

    function doAction($noEcho=false){
        $person = Person::newFromName($_POST['user_name']);
	$person->firstName = $_POST['fname'];
	$person->middleName = $_POST['mname'];
	$person->lastName = $_POST['lname'];
	$person->realname = "{$_POST['fname']} {$_POST['mname']} {$_POST['lname']}";
	$person->update();
   }

   function isLoginRequired(){
                return true;
   }
}
?>
