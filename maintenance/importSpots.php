<?php

    /*
        USRIS
        [{"id": "5022", "votes": [2,0,2,4,5]},
         {"id": "23", "votes": [1,1,4,4,3]},
         {"id": "24", "votes": [1,0,4,4,4]},
         {"id": "25", "votes": [2,0,0,8,3]},
         {"id": "221", "votes": [1,1,4,5,2]},
         {"id": "21", "votes": [1,1,4,5,2]},
         {"id": "5674", "votes": [1,0,5,4,3]},
         {"id": "26", "votes": [1,1,4,6,1]},
         {"id": "9", "votes": [1,0,2,5,5]},
         {"id": "51", "votes": [1,0,4,5,3]}
        ]
    */

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);
    
    $spots = array_map("str_getcsv", file("spots.csv"));
    
    $toUpdate = array();
    $uniqueQs = array();
    
    foreach($spots as $spot){
        $emplid = $spot[9];
        $termId = DBFunctions::escape($spot[3]);
        $classNum = DBFunctions::escape($spot[5]);
        $enrolled = $spot[11];
        $responses = $spot[12];
        $qId = $spot[13];
        $qText = $spot[14];
        $rsp1 = (int)$spot[15];
        $rsp2 = (int)$spot[16];
        $rsp3 = (int)$spot[17];
        $rsp4 = (int)$spot[18];
        $rsp5 = (int)$spot[19];
        
        $uniqueQs[$qId] = $qText;
        
        $person = Person::newFromEmployeeId($emplid);
        if($person->getId() != 0){
            $user_course = DBFunctions::execSQL("SELECT uc.*
                                                 FROM grand_user_courses uc, grand_courses c
                                                 WHERE c.`Term` = '{$termId}'
                                                 AND c.`Class Nbr` = '{$classNum}'
                                                 AND uc.user_id = '{$person->getId()}'
                                                 AND c.id = uc.course_id");
            if(count($user_course) > 0){
                $toUpdate[$user_course[0]['id']][] = array("id" => $qId, 
                                                           "enrolled" => $enrolled, 
                                                           "responses" => $responses, 
                                                           "question" => $qText, 
                                                           "votes" => array($rsp1, $rsp2, $rsp3, $rsp4, $rsp5));
            }
        }
    }
    
    echo "\n";
    foreach($toUpdate as $id => $data){
        DBFunctions::update('grand_user_courses',
                            array('course_evals' => json_encode($data)),
                            array('id' => $id));
    }
    
    echo "Updated ".count($toUpdate)." evals\n\n";
    
    print_r($uniqueQs);

?>
