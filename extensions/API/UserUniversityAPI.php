<?php

class UserUniversityAPI extends API{

    function UserUniversityAPI(){
        $this->addPOST("university", false, "The name of the university", "University of Alberta");
        $this->addPOST("department", false, "The department the user is in", "Computing Science");
        $this->addPOST("title", false, "The title of the user (ie. Professor)", "Professor");
    }

    function processParams($params){
        $_POST['university'] = @$_POST['university'];
        if(!isset($_POST['department'])){
            $_POST['department'] = "";
        }
        if(!isset($_POST['title'])){
            $_POST['title'] = "";
        }
    }

    function doAction($noEcho=false){
        if(!isset($_POST['university'])){
            $_POST['university'] = Person::getDefaultUniversity();
        }
        if(!isset($_POST['title'])){
            $_POST['title'] = Person::getDefaultPosition();
        }
        $data = DBFunctions::select(array('grand_universities'),
                                    array('university_name'),
                                    array('university_name' => $_POST['university']));
        if(count($data) == 0){
            DBFunctions::insert('grand_universities',
                                array('university_name' => $_POST['university'],
                                      '`order`' => 10000,
                                      '`default`' => 0));
        }
        $data = DBFunctions::select(array('grand_positions'),
                                    array('position'),
                                    array('position' => $_POST['title']));
        if(count($data) == 0){
            DBFunctions::insert('grand_positions',
                                array('position' => $_POST['title'],
                                      '`order`' => 10000,
                                      '`default`' => 0));
        }
        $universities = Person::getAllUniversities();
        foreach($universities as $id => $uni){
            if($uni == $_POST['university']){
                $_POST['university'] = $id;
                break;
            }
        }
        $positions = Person::getAllPositions();
        foreach($positions as $id => $pos){
            if($pos == $_POST['title']){
                $_POST['title'] = $id;
                break;
            }
        }
        $person = Person::newFromName($_POST['user_name']);
        
        $data = DBFunctions::select(array('grand_user_university'),
                                    array('id',
                                          'university_id', 
                                          'department',
                                          'position_id'),
                                    array('user_id' => EQ($person->getId())),
                                    array('id' => 'DESC'),
                                    array(1));
        if(count($data) > 0){
            //Update Previous
            $row = $data[0];
            $last_id = $row['id'];
            if($row['university_id'] == $_POST['university'] &&
               $row['department'] == $_POST['department'] &&
               $row['position_id'] == $_POST['title']){
               if(!$noEcho){
                    echo "No Change in University Information\n";
                }
            }
            else{
                $uni = $person->getUni();
                DBFunctions::update('grand_user_university',
                                    array('end_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                                    array('id' => EQ($last_id)));
                MailingList::unsubscribeAll($person);
                //Insert New
                DBFunctions::insert('grand_user_university',
                                    array('user_id' => $person->getId(),
                                          'university_id' => $_POST['university'],
                                          'department' => $_POST['department'],
                                          'position_id' => $_POST['title'],
                                          'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
                Person::$universityCache = array();
                $person->university = false;
                MailingList::subscribeAll($person);
                if(!$noEcho){
                    echo "Account University Updated\n";
                }
            }
        }
        else{
            //Insert New
            DBFunctions::insert('grand_user_university',
                                array('user_id' => $person->getId(),
                                      'university_id' => $_POST['university'],
                                      'department' => $_POST['department'],
                                      'position_id' => $_POST['title'],
                                      'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
            foreach(MailingList::getListByUniversity($_POST['university']) as $list){
                MailingList::subscribe($list, $person);
            }
            if(!$noEcho){
                echo "Account University Added\n";
            }
        }
        $person->university = false;
        Person::$universityCache = array();
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
