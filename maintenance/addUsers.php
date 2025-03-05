<?php

require_once('commandLine.inc');
global $wgUser;

$wgUser = User::newFromId(1);

// Staff
$people = array(array("Robert.Wood", "rtwood@ualberta.ca"));

foreach($people as $person){
    $name = $person[0];
    $realName = str_replace(".", " ", $name);
    $email = $person[1];
    
    User::createNew($name, array('real_name' => $realName, 
                                 'password' => User::crypt(mt_rand()), 
                                 'email' => $email));
}

/*foreach($people as $person){
    $p = Person::newFromName($person[0]);
    DBFunctions::insert('grand_roles',
                        array('user_id' => $p->getId(),
                              'role' => "Staff",
                              'start_date' => "2017-11-01 00:00:00"));
}*/
   
?>
