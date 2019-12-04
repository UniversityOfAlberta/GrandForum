<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);

    DBFunctions::execSQL("UPDATE grand_relations SET university = 0", true);
    $rels = DBFunctions::execSQL("SELECT * FROM grand_relations");
    
    foreach($rels as $rel){
        $relStart = substr($rel['start_date'], 0, 10);
        $relEnd = str_replace("0000-00-00", "9999-99-99", substr($rel['end_date'], 0, 10));
        
        $unis = DBFunctions::execSQL("SELECT * FROM grand_user_university WHERE user_id = {$rel['user2']}");
        $found = false;
        foreach($unis as $uni){
            $uniStart = substr($uni['start_date'], 0, 10);
            $uniEnd = str_replace("0000-00-00", "9999-99-99", substr($uni['end_date'], 0, 10));
            
            if(($relStart <= $uniStart && $relEnd >= $uniStart) ||
               ($relStart >= $uniStart && $relStart <= $uniEnd && $relEnd >= $uniStart)){
                $found = true;
                DBFunctions::execSQL("UPDATE grand_relations SET university = {$uni['id']} WHERE id = {$rel['id']}", true);
                break;
            }
        }
    }
    
?>
