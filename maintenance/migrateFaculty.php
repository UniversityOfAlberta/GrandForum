<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

$data = DBFunctions::select(array('grand_user_university'),
                            array('*'),
                            array());

foreach($data as $row){
    $dept = $row['department'];
    $fac = $row['faculty'];
    if(strstr($dept, " / ") !== false && $fac == ""){
        $exploded = explode(" / ", $dept);
        DBFunctions::update('grand_user_university',
                            array('faculty'    => $exploded[0],
                                  'department' => $exploded[1]),
                            array('id' => EQ($row['id'])));
    }
}

?>
