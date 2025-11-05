<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    $allPeople = Person::getAllPeople();
    foreach($allPeople as $person){
        if($person instanceof FullPerson){
            $person->getFecPersonalInfo();
            $dept = $person->getDepartment();
            $mydepts = array_keys($person->departments);
            $faculty = "";
            foreach($person->getUniversities() as $uni){
                $dept = str_replace("Faculty of ", "", $uni['department']);
                $faculty = @Person::$facultyMap[$dept];
                if($faculty == getFaculty()){
                    $check = DBFunctions::select(array('grand_personal_fec_info'),
                                                 array('*'),
                                                 array('user_id' => $person->getId()));
                    if(count($check) == 0){
                        DBFunctions::insert('grand_personal_fec_info',
                                            array('user_id' => $person->getId()));
                    }
                    else if($check[0]['faculty'] == ""){
                        if(!in_array($facultyMapSimple[$faculty], $facultyMapSimple[$faculty])){
                            $highest = 0;
                            $closest = "";
                            foreach($facultyMapSimple[$faculty] as $d){
                                $diff = similar_text($d, $dept);
                                if($diff > $highest){
                                    $highest = $diff;
                                    $closest = $d;
                                }
                            }
                            $dept = $closest;
                        }
                        if($dept != ""){
                            DBFunctions::update('grand_personal_fec_info',
                                                array('faculty' => $faculty,
                                                      'departments' => json_encode(array($dept => 100))),
                                                array('user_id' => $person->getId()));
                        }
                        echo "{$person->getName()}: {$faculty} / {$dept}\n";
                    }
                    break;
                }
            }
        }
    }
    
?>
