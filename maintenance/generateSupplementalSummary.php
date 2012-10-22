<?php

require_once('commandLine.inc');

$pnis = Person::getAllPeople(PNI);
foreach($pnis as $pni){
    showInfo($pni);
}

$cnis = Person::getAllPeople(CNI);
foreach($cnis as $cni){
    showInfo($cni);
}

function showInfo($person){
    
    $sql = "SELECT *
            FROM `mw_session_data`
            WHERE `handle` = 3
            AND `user_id` = '{$person->getId()}'";
    $data = DBFunctions::execSQL($sql);
    if(count($data) > 0){
        echo 
"
============================
=== {$person->getName()} ===
============================
Timestamp: {$data[0]['timestamp']}\n";
        print_r(unserialize($data[0]['data']));
    }
}

?>
