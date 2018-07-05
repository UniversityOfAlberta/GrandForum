<?php

    // necessary code for commandline use
    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);

    $faculty = DBFunctions::execSQL("SELECT * FROM bddEfec2_production.faculty_staff_members");
    $eFECourses = DBFunctions::execSQL("SELECT * FROM bddEfec2_production.courses");
    $coursesIndex = array();
    $facultyIndex = array();
    $courses = DBFunctions::execSQL("SELECT * FROM grand_courses");
    
    // Pre process data
    foreach($faculty as $fac){
        $facultyIndex[$fac['id']] = $fac;
    }
    foreach($eFECourses as $course){
        $coursesIndex[$course['class_number']] = $course;
    }
    
    // Update percentages
    foreach($courses as $course){
        if(isset($coursesIndex[$course['Class Nbr']])){
            $person = Person::newFromEmployeeId($facultyIndex[$coursesIndex[$course['Class Nbr']]['faculty_staff_member_id']]['uid']);
            DBFunctions::update('grand_user_courses',
                                array('percentage' => $coursesIndex[$course['Class Nbr']]['percentage']),
                                array('user_id' => EQ($person->getId()),
                                      'course_id' => EQ($course['id'])));
            echo "{$person->getName()}: {$course['Subject']} {$course['Catalog']} ({$coursesIndex[$course['Class Nbr']]['percentage']}%)\n";
        }
    }
?>
