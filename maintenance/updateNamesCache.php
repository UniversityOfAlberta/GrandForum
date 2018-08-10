<?php
    require_once('commandLine.inc');
    $wgUser = User::newFromId(1);
    
    DBFunctions::execSQL("DELETE FROM `grand_names_cache` WHERE user_id NOT IN (SELECT user_id FROM mw_user)", true);
    DBFunctions::execSQL("DELETE FROM `grand_names_cache` WHERE user_id IN (SELECT user_id FROM mw_user WHERE deleted = 1)", true);
    
    $people = Person::getAllPeople();
    $iterationsSoFar = 0;
    foreach($people as $person){
        $person->updateNamesCache();
        show_status(++$iterationsSoFar, count($people));
    }
    
?>
