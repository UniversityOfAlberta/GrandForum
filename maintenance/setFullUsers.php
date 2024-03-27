<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $allPeople = Person::getAllPeople();
    foreach($allPeople as $person){
        $roles = $person->getRoles(true);
        $full = false;
        foreach($roles as $role){
            if($role->getRole() != HQP){
                $full = true;
            }
        }
        if($full){
            DBFunctions::update('mw_user',
                                array('full' => 1),
                                array('user_id' => $person->getId()));
        }
    }
    
?>
