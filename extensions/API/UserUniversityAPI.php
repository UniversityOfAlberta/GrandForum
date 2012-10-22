<?php

class UserUniversityAPI extends API{

    function UserUniversityAPI(){
        $this->addPOST("university", false, "The name of the university", "University of Alberta");
        $this->addPOST("department", false, "The department the user is in", "Computing Science");
        $this->addPOST("title", false, "The title of the user (ie. Professor)", "Professor");
    }

    function processParams($params){
        if(isset($_POST['university'])){
            $_POST['university'] = str_replace("'", "&#39;", $_POST['university']);
        }
        if(!isset($_POST['department'])){
            $_POST['department'] = "";
        }
        else{
            $_POST['department'] = str_replace("'", "&#39;", $_POST['department']);
        }
        if(!isset($_POST['title'])){
            $_POST['title'] = "";
        }
        else{
            $_POST['title'] = str_replace("'", "&#39;", $_POST['title']);
        }
    }

	function doAction($noEcho=false){
        if(isset($_POST['university'])){
            $sql = "SELECT * 
                    FROM mw_universities";
            $rows = DBFunctions::execSQL($sql);
		    foreach($rows as $row){
			    $rows[] = $row;
			    if($row['university_name'] == $_POST['university']){
			        $found = true;
			        $_POST['university'] = $row['university_id'];
			    }
		    }
            if(!$noEcho){
                if(!$found){
                    echo "This University does not exist in the system.  Your choices are:\n\n";
                    foreach($rows as $row){
                        echo "-{$row['university_name']}\n";
                    }
                    echo "If your university is not listed, then please contact support@forum.grand-nce.ca\n";
                }
		    }
		}
		else{
		    $_POST['university'] = "";
		}
        $person = Person::newFromName($_POST['user_name']);
        
        $sql = "SELECT id 
                FROM mw_user_university
                WHERE user_id = '{$person->getId()}'
				ORDER BY id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        $count = count($data);
	    if($count > 0){
	        //Update Previous
	        $row = $data[0];
			$last_id = $row['id'];
			
			$sql = "UPDATE mw_user_university
					SET end_date = CURRENT_TIMESTAMP
					WHERE id = '{$last_id}'";
            DBFunctions::execSQL($sql, true);
			
			//Insert New
	        $sql = "INSERT INTO mw_user_university (user_id, university_id, department, position, start_date)
					VALUES('{$person->getId()}','{$_POST['university']}','{$_POST['department']}','{$_POST['title']}', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);
            if(!$noEcho){
                echo "Account University Updated\n";
            }
	    }
	    else{
	        //Insert New
	        $sql = "INSERT INTO mw_user_university (user_id, university_id, department, position, start_date)
					VALUES('{$person->getId()}','{$_POST['university']}','{$_POST['department']}','{$_POST['title']}', CURRENT_TIMESTAMP)";
            DBFunctions::execSQL($sql, true);
            if(!$noEcho){
                echo "Account University Added\n";
            }
	    }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>
