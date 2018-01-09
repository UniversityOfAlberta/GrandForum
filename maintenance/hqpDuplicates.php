<?php

require_once('commandLine.inc');
$wgUser = User::newFromId(1);
$i = 1;

while(file_exists("hqpDuplicates$i.csv")){
    $csv = explode("\n", file_get_contents("hqpDuplicates$i.csv"));

    $people = array();
    foreach($csv as $line){
        $cells = str_getcsv($line);
        if(count($cells) > 1){
            $id = $cells[0];
            $dupId = $cells[1];
            $correct = $cells[2];
            $name = $cells[3];
            $realName = $cells[4];
            if($dupId != ""){
                $person = Person::newFromName($name);
                $people[$id] = array('id' => $id, 
                                     'dupId' => $dupId, 
                                     'correct' => $correct, 
                                     'name' => $name, 
                                     'realName' => $realName, 
                                     'person' => $person);
            }
        }
    }

    foreach($people as $data){
        if($data['correct'] == 0){
            $hqp = $data['person'];
            $dupHQP = $people[$data['dupId']]['person'];
            if($hqp != null && $dupHQP != null &&
               $hqp->getId() != 0 && $dupHQP->getId() != 0){
               echo "{$hqp->getName()} -> {$dupHQP->getName()}\n";
                if($dupHQP->getEmail() == ""){
                    DBFunctions::update('mw_user',
                                        array('user_email' => $hqp->getEmail()),
                                        array('user_id' => $dupHQP->getId()));
                }
                
                DBFunctions::update('grand_relations',
                                    array('user2' => $dupHQP->getId()),
                                    array('user2' => $hqp->getId()));
                                    
                DBFunctions::update('grand_roles',
                                    array('user_id' => $dupHQP->getId()),
                                    array('user_id' => $hqp->getId()));
                                    
                DBFunctions::update('grand_user_university',
                                    array('user_id' => $dupHQP->getId()),
                                    array('user_id' => $hqp->getId()));
                                    
                DBFunctions::update('mw_user',
                                    array('deleted' => 1),
                                    array('user_id' => $hqp->getId()));
                $products = $hqp->getPapers("all", true, 'both', false, 'Public');
                foreach($products as $product){
                    $changed = false;
                    $authors = unserialize($product->authors);
                    foreach($authors as $key => $author){
                        if($author == $hqp->getId()){
                            $authors[$key] = $dupHQP->getId();
                            $changed = true;
                        }
                    }
                    if($changed){
                        echo "\tUPDATE Product: {$product->getId()}\n";
                        DBFunctions::update('grand_products',
                                            array('authors' => serialize($authors)),
                                            array('id' => $product->getId()));
                    }
                }
            }
        }
    }
    $i++;
}

$people = Person::getAllPeople();
foreach($people as $person){
    if($person->isRoleDuring(HQP, "1900-00-00", "2100-00-00")){
        $universities = $person->getUniversities();
        $unis = array();
        foreach($universities as $uni){
            $unis[$uni['university'].$uni['department'].$uni['position']][] = $uni;
        }
        
        // Merge University Entries
        foreach($unis as $uni){
            $minDate = "9999-99-99";
            $maxDate = "0000-00-00";
            $first = $uni[0];
            foreach($uni as $key => $u){
                $minDate = min($minDate, $u['start']);
                $maxDate = max($maxDate, $u['end']);
                if($key > 0){
                    DBFunctions::delete('grand_user_university',
                                        array('id' => $u['id']));
                }
            }
            DBFunctions::update('grand_user_university',
                                array('start_date' => $minDate,
                                      'end_date' => $maxDate),
                                array('id' => $first['id']));
        }
        
        $universities = $person->getUniversities();
        $unis = array();
        foreach($universities as $uni){
            $unis[$uni['position']] = $uni;
        }
        
        foreach($unis as $uni){
            if($uni['end'] == "0000-00-00 00:00:00"){
                
                if($uni['position'] == "Undergraduate" && (isset($unis["Graduate Student - Master's Course"]) || isset($unis["Graduate Student - Master's Thesis"]))){
                    echo "{$person->getName()}\n";
                    $otherUni = (isset($unis["Graduate Student - Master's Course"])) ? $unis["Graduate Student - Master's Course"] : $unis["Graduate Student - Master's Thesis"];
                    DBFunctions::update('grand_user_university',
                                        array('end_date' => $otherUni['start']),
                                        array('id' => $uni['id']));
                }
            }
        }
    }
}

?>
