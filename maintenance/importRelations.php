<?php

require_once('commandLine.inc');
global $wgUser;

$wgUser = User::newFromId(1);

$relations = array_map("str_getcsv", file("relations.csv"));

foreach($relations as $rel){
    $start = $rel[0];
    $end = $rel[1];
    $role = $rel[2];
    $name = $rel[3];
    $ccid = $rel[4];
    $dept = $rel[5];
    $supStart = explode("|", $rel[6]);
    $supEnd = explode("|", $rel[7]);
    $supName = explode("|", $rel[8]);
    $supCCID = explode("|", $rel[9]);
    
    $hqp = Person::newFromEmail("$ccid@ualberta.ca");
    if($hqp->getId() != 0){
        $unis = $hqp->getUniversities();
        if(count($unis) == 1){
            $uni = $unis[0];
            DBFunctions::update('grand_user_university',
                                array('start_date' => $start,
                                      'end_date' => $end),
                                array('id' => $uni['id']));
            foreach($supCCID as $i => $supId){
                $sup = Person::newFromEmail("$supId@ualberta.ca");
                if($sup->getId() != 0 && 
                   $sup instanceof FullPerson && 
                   !$sup->isRelatedToDuring($hqp, SUPERVISES_BOTH, "1900-01-01", "2100-01-01")){
                    $sStart = ($supStart[$i] == "NULL") ? $start : $supStart[$i];
                    $sEnd   = ($supEnd[$i] == "NULL")   ? $end   : $supEnd[$i];
                    echo "{$ccid} -> {$supId}: {$sStart} - {$sEnd}\n";
                    DBFunctions::insert('grand_relations',
                                        array('user1' => $sup->getId(),
                                              'user2' => $hqp->getId(),
                                              'university' => $uni['id'],
                                              'type' => SUPERVISES,
                                              'start_date' => $sStart,
                                              'end_date' => $sEnd));
                }
            }
        }
    }
    
}
   
?>
