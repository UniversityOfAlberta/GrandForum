<?php
require_once 'commandLine.inc';
$students = array();
$hqps = Person::getAllPeople(HQP);
foreach($hqps as $hqp){
    $university = $hqp->getUniversity();
    
    $position = strtolower($university['position']);
    if($position == 'undergraduate' ||
       $position == 'masters student' ||
       $position == 'phd student' ||
       $position == 'postdoc'){
        echo $hqp->getEmail()."\n";   
   }
}

?>
