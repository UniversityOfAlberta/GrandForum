<?php
	//written to input faculty of science professors to database
    require_once( "commandLine.inc" );
	//functions and code based on install.php
    $wgUser=User::newFromName("Admin");
    if(file_exists("cmputCourseInfo.csv")){
    	print_r("Reading in data");
        $count = 0;
	$lines = explode("\n", file_get_contents("cmputCourseInfo.csv"));
        foreach($lines as $line){
             $cells = str_getcsv($line);
             if(count($cells) > 1){
                $email = @trim($cells[26]);
		$name = explode(",",@trim($cells[24]));
		$number = @trim($cells[3]);
		$sql = "SELECT id FROM `grand_courses` WHERE `Class Nbr` = '$number'"; 
		$data = DBFunctions::execSQL($sql);
		//print_r("NUMBER: $number \n");
		
		if($name[0] != "" && count($data)==1){
		    $courseId = $data[0]['id'];
		    $firstname=$name[1];
		    $lastname=$name[0];
		    $fullname = "$firstname $lastname";
                    $person = Person::newFromNameLike($fullname);
		    $id = $person->getId();
		    if($id ==0){
			$person = Person::newFromAlias($fullname);
			$id = $person->getId();
			if($id==0){
			    $person = Person::newFromEmail($email);
			    if($person !=null){
			        $id = $person->getId();
			    }
			    else{
			        //print_r("couldnt find $fullname, $email");
			        continue;
			    }
			}
		    }
		    $sql = "INSERT INTO `grand_user_courses`(user_id,course_id) VALUES($id,$courseId)";
		    DBFunctions::execSQL($sql,true);

		}
	
		//if($email != ""){
		//$person = Person::newFromEmail($email);
	     	//print_r($person);
		//if($person == null){
		//    print_r("NULL $email\n");
		//}
		//}
	     }
    	}
        print_r("inserted data");
     }
     else{
	print_r("error reading file");
     }
?>
