<?php
    require_once('commandLine.inc');
    $people = Person::getAllPeople();
    foreach($people as $person){
        $user = User::newFromId($person->getId());
        //$timestamp = $user->getEmailAuthenticationTimestamp();
        $sql = "SELECT `user_registration`
                FROM `mw_user`
                WHERE `user_id` = '{$person->getId()}'";
        $data = DBFunctions::execSQL($sql);
        $timestamp = $data[0]['user_registration'];
        if($timestamp != ""){
            $year = substr($timestamp, 0, 4);
            $month = substr($timestamp, 4, 2);
            $day = substr($timestamp, 6, 2);
            $hour = substr($timestamp, 8, 2);
            $minute = substr($timestamp, 10, 2);
            $second = substr($timestamp, 12, 2);
            
            $formatted_date = "$year-$month-$day $hour:$minute:$second";
            
            $sql = "UPDATE `grand_user_projects`
                    SET `start_date` = '$formatted_date'
                    WHERE `user` = '{$person->getId()}'
                    AND `start_date` LIKE '2011-07-29%'
                    ORDER BY id ASC";
            DBFunctions::execSQL($sql, true);
            echo "{$person->getName()}:\n\t $formatted_date\n";
        }
    }
?>
