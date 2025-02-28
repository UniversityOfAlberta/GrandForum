<?php
    require_once('commandLine.inc');
    $wgUser = User::newFromId(1);
    
    function update(&$inserts, &$deletes){
        DBFunctions::execSQL("DELETE FROM `grand_names_cache` WHERE user_id IN (".implode(",", $deletes).")", true);
        DBFunctions::execSQL("INSERT INTO `grand_names_cache` (`name`, `user_id`) VALUES ".implode(", ", $inserts), true);
    }
    
    DBFunctions::execSQL("DELETE FROM `grand_names_cache` WHERE user_id NOT IN (SELECT user_id FROM mw_user)", true);
    DBFunctions::execSQL("DELETE FROM `grand_names_cache` WHERE user_id IN (SELECT user_id FROM mw_user WHERE deleted = 1)", true);
    
    $people = Person::getAllPeople();
    $iterationsSoFar = 0;
    $inserts = array();
    $deletes = array();
    foreach($people as $person){
        foreach($person->updateNamesCache(true) as $insert){
            $inserts[] = $insert;
        }
        $deletes[] = $person->getId();
        if(count($deletes) > 100){
            update($inserts, $deletes);
            $inserts = array();
            $deletes = array();
        }
        show_status(++$iterationsSoFar, count($people) + 1);
    }
    update($inserts, $deletes);
    show_status(++$iterationsSoFar, count($people) + 1);
    
?>
