<?php

    require_once('commandLine.inc');
    $wgUser = User::newFromId(1);
    
    $data = DBFunctions::execSQL("SELECT user_email, COUNT(*) as count
                                  FROM `mw_user` 
                                  WHERE deleted = 0 
                                  AND candidate = 0 
                                  AND user_email != ''
                                  AND user_name != 'Admin'
                                  GROUP BY user_email 
                                  HAVING COUNT(*) > 1
                                  ORDER BY COUNT(*) DESC");
    $count = count($data);
    $sum = 0;
    foreach($data as $row){
        $sum += $row['count'];
    }
    echo "\nFound $count sets, {$sum} users\n";
    echo "\n=== Instructions ===\n";
    echo "\tType the id of the user to keep, or type 's' or 'skip' to skip the current set\n\n";
    
    foreach($data as $row){
        echo "\nDuplicates For: {$row['user_email']}\n";
        echo "=============================================================================================================================================================\n";
        $email = DBFunctions::escape($row['user_email']);
        $people = DBFunctions::execSQL("SELECT *
                                        FROM `mw_user`
                                        WHERE deleted = 0
                                        AND candidate = 0
                                        AND user_email = '{$email}'
                                        AND user_name != 'Admin'");
        $ids = array();
        foreach($people as $person){
            $ids[] = $person['user_id'];
            $sups = array();
            $p = Person::newFromId($person['user_id']);
            $supsData = DBFunctions::select(array('grand_relations'),
                                            array('*'),
                                            array('user2' => $p->getId()));
            foreach($supsData as $supRow){
                $sups[] = $supRow['user1'];
            }
            $sups = implode(", ", array_unique($sups));
            printf("%6s: %-30s | %-8s | %-8s | %-30s | %-36s | %-20s |\n", $p->getId(), $p->getName(), $p->getEmployeeId(), $p->getType(), $p->getDepartment(), str_replace("&#39;", "'", $p->getPosition()), $sups);
        }
        echo "=============================================================================================================================================================\n";
        do {
            $keepId = readline("Which is the correct User Id? ");
        } while(!in_array($keepId, $ids) && ($keepId != "s" && $keepId != "skip"));
        if($keepId == "s" || $keepId == "skip"){
            continue;
        }
        foreach($ids as $id){
            if($id != $keepId){
                system("php mergeUsers.php {$id} {$keepId}");
            }
        }
    }

?>
