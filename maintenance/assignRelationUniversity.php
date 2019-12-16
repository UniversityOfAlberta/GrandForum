<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromId(1);

    DBFunctions::execSQL("UPDATE grand_relations SET university = 0", true);
    $rels = DBFunctions::execSQL("SELECT * FROM grand_relations");
    
    foreach($rels as $rel){
        $relStart = substr($rel['start_date'], 0, 10);
        $relEnd = str_replace("0000-00-00", "2100-01-01", substr($rel['end_date'], 0, 10));
        
        $unis = DBFunctions::execSQL("SELECT * FROM grand_user_university WHERE user_id = {$rel['user2']}");
        $found = false;
        $minUni = null;
        $minInterval = 10000000;
        $minInterval2 = 10000000;
        
        $valid = array();
        foreach($unis as $uni){
            $uniStart = substr($uni['start_date'], 0, 10);
            $uniEnd = str_replace("0000-00-00", "2100-01-01", substr($uni['end_date'], 0, 10));
            
            if(($relStart <= $uniStart && $relEnd >= $uniStart) ||
               ($relStart >= $uniStart && $relStart <= $uniEnd && $relEnd >= $uniStart)){
                // Found a possible universtiy, now check to see how close the relation and university match up
                $start1 = new DateTime($uniStart);
                $start2 = new DateTime($relStart);
                
                $end1 = new DateTime($uniEnd);
                $end2 = new DateTime($relEnd);
                
                $startInterval = intval($start1->diff($start2)->format('%a')); // Difference in days
                $endInterval = intval($end1->diff($end2)->format('%a')); // Difference in days
                
                if($startInterval < $minInterval){
                    // A new lowest has been found, reset the minInterval2 and calculate new minInterval2
                    $minInterval2 = 10000000;
                    $minInterval2 = min($minInterval2, $endInterval);
                }
                else if($startInterval == $minInterval){
                    // This one is equal start date to a previous university, calculate new minInterval2
                    $minInterval2 = min($minInterval2, $endInterval);
                }
                
                $minInterval = min($minInterval, $startInterval);
                $valid[] = $uni;
                if($minInterval == $startInterval && $minInterval2 == $endInterval){
                    // Closest match so far, so use this one for now
                    $minUni = $uni;
                }
            }
        }
        
        /*if(count($valid) > 1){
            echo "$relStart - $relEnd (Ambiguious)\n";
            foreach($valid as $uni){
                $uniStart = substr($uni['start_date'], 0, 10);
                $uniEnd = str_replace("0000-00-00", "2100-01-01", substr($uni['end_date'], 0, 10));
                if($minUni == $uni){
                    echo "\t{$uniStart} - {$uniEnd} <--\n";
                }
                else{
                    echo "\t{$uniStart} - {$uniEnd}\n";
                }
            }
            echo "\n";
        }*/
        
        if($minUni != null){
            // Use the university with the lowest difference in start dates
            DBFunctions::execSQL("UPDATE grand_relations SET university = {$minUni['id']} WHERE id = {$rel['id']}", true);
        }
        /*else{
            echo "$relStart - $relEnd (No Match)\n";
            foreach($unis as $uni){
                $uniStart = substr($uni['start_date'], 0, 10);
                $uniEnd = str_replace("0000-00-00", "2100-01-01", substr($uni['end_date'], 0, 10));
                if($minUni == $uni){
                    echo "\t{$uniStart} - {$uniEnd} <--\n";
                }
                else{
                    echo "\t{$uniStart} - {$uniEnd}\n";
                }
            }
            echo "\n";
        }*/
    }
    
?>
