<?php

require_once('commandLine.inc');

function addUniversity($user, $university, $department, $title){
    $_POST['user_name'] = $user->getName();
    $_POST['university'] = $university;
    $_POST['department'] = $department;
    $_POST['title'] = $title;

    echo "{$_POST['user_name']}: {$_POST['university']}, {$_POST['department']}, {$_POST['title']}\n";

    $api = new UserUniversityAPI(true);
    $api->doAction();
}

$deptHash = array('@cs.' => "Computing Science",
                  '@csc.' => "Computing Science",
                  '@ece.' => "Electrical and Computer Engineering",
                  '@eecs.' => "School of Electrical Engineering and Computer Science",
                  '@site.' => "School of Electrical Engineering and Computer Science",
                  '@sala.' => "Architecture & Landscape Architecture",
                  '@arch.' => "Architecture & Landscape Architecture",
                  '@civil.' => "Engineering",
                  '@mie.' => "Mechanical and Industrial Engineering",
                  '@nursing.' => "Nursing",
                  '@cim.' => "Centre for Intelligent Machines",
                  '@encs.' => "Computer Science & Engineering");

$uniHash = array('ualberta.ca' => "University of Alberta",
                 'ecuad.ca' => "Emily Carr University of Art and Design",
                 'carleton.ca' => "Carleton University",
                 'sfu.ca' => "Simon Fraser University",
                 'ocad.ca' => "Ontario College of Art & Design",
                 'ocadu.ca' => "Ontario College of Art & Design",
                 'ubc.ca' => "University of British Columbia",
                 'uwaterloo.ca' => "University of Waterloo",
                 'queensu.ca' => "Queen`s University",
                 'ryerson.ca' => "Ryerson University",
                 'mcgill.ca' => "McGill University",
                 'uwo.ca' => "University of Western Ontario",
                 'uvic.ca' => "University of Victoria",
                 'dal.ca' => "Dalhousie University",
                 'umanitoba.ca' => "University of Manitoba",
                 'concordia.ca' => "Concordia University",
                 'utoronto.ca' => "University of Toronto",
                 'dgp.toronto.edu' => "University of Toronto",
                 'usask.ca' => "University of Saskatchewan",
                 'mcmaster.ca' => "McMaster University",
                 'nscad.ca' => "Nova Scotia College of Art and Design",
                 'umontreal.ca' => "University of Montreal",
                 'yorku.ca' => "York University",
                 'uottawa.ca' => "University of Ottawa",
                 'uoit.ca' => "University of Ontario Institute of Technology",
                 'uoit.net' => "University of Ontario Institute of Technology",
                 'ucalgary.ca' => "University of Calgary",
                 'wlu.ca' => "Wilfrid Laurier University",
                 'royalroads.ca' => "Royal Roads University",
                 'athabascau.ca' => "Athabasca University",
                 'mun.ca' => "Memorial University of Newfoundland",
                 'uleth.ca' => "University of Lethbridge");      

$people = Person::getAllPeople('all');
foreach($people as $person){
    $uni = $person->getUni();
    $dept = $person->getDepartment();
    $uni2 = $uni;
    $dept2 = $dept;
    if($uni == "" || $uni == "Unknown"){
        foreach($uniHash as $key => $university){
            if(strstr(strtolower($person->getEmail()), $key) !== false){
                $uni2 = $university;
                break;
            }
        }
    }
    if($dept == "" || $dept == "Unknown" || $dept == "Other"){
        foreach($uniHash as $key => $university){
            if(strstr(strtolower($person->getEmail()), $key) !== false){
                foreach($deptHash as $key1 => $department){
                    if(strstr(strtolower($person->getEmail()), $key1) !== false){
                        $dept2 = $department;
                        break;
                    }
                }
                break;
            }
        }
    }
    if(($dept == "" || $dept == "Unknown" || $dept == "Other" ||
       $uni == "" || $uni == "Unknown") &&
       ($dept2 != $dept || $uni2 != $uni)){
        addUniversity($person, $uni2, $dept2, $person->getPosition());
    }
}

$hqps = Person::getAllPeople(HQP);
foreach($hqps as $hqp){
    $uni = $hqp->getUni();
    $dept = $hqp->getDepartment();
    $uni2 = $uni;
    $dept2 = $dept;
    if($uni == "" || $uni == "Unknown"){
        $unis = array();
        foreach($hqp->getSupervisors() as $sup){
            if($sup->getUni() != "" && $sup->getUni() != "Unknown"){
                $unis[$sup->getUni()] = $sup->getUni();
            }
        }
        if(count($unis) == 1){
            $uni2 = implode($unis);
        }
    }
    if($dept == "" || $dept == "Unknown" || $dept == "Other"){
        $depts = array();
        foreach($hqp->getSupervisors() as $sup){
            if($sup->getDepartment() != "" && $sup->getDepartment() != "Unknown" && $sup->getDepartment() != "Other"){
                $depts[$sup->getDepartment()] = $sup->getDepartment();
            }
        }
        if(count($depts) == 1){
            $dept2 = implode($depts);
        }
    }
    if(($dept == "" || $dept == "Unknown" || $dept == "Other" ||
       $uni == "" || $uni == "Unknown") &&
       ($dept2 != $dept || $uni2 != $uni)){
        addUniversity($hqp, $uni2, $dept2, $person->getPosition());
    }
}

?>
