<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $allPeople = Person::getAllPeople();
    foreach($allPeople as $person){
        if($person instanceof FullPerson){
            $person->getFecPersonalInfo();
            $dept = $person->getDepartment();
            $faculty = "";
            foreach($person->getUniversities() as $uni){
                $dept = $uni['department'];
                $faculty = @Person::$facultyMap[$dept];
                if($faculty != ""){
                    $check = DBFunctions::select(array('grand_personal_fec_info'),
                                                 array('*'),
                                                 array('user_id' => $person->getId()));
                    if(count($check) == 0){
                        DBFunctions::insert('grand_personal_fec_info',
                                            array('user_id' => $person->getId()));
                    }
                    DBFunctions::update('grand_personal_fec_info',
                                        array('faculty' => $faculty,
                                              'departments' => json_encode(array($dept => 100))),
                                        array('user_id' => $person->getId()));
                    echo "{$person->getName()}: {$faculty}\n";
                    break;
                }
            }
            if($faculty == ""){
                echo "MISSING {$person->getName()}: {$dept}\n";
            }
        }
    }
    
?>
