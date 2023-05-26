<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $start = (YEAR-1)."-07-01";
    $end = (YEAR)."-07-01";
    
    $allPeople = array_merge(Person::getAllPeopleDuring(NI, $start, $end),
                             Person::getAllPeopleDuring("ATS", $start, $end));
    
    $file = file_get_contents("News Brief.mbox");
    $file = str_replace("=\r\n", "", $file);
    
    $exploded = explode("Content-Type: text/plain;", $file);
    
    DBFunctions::execSQL("TRUNCATE TABLE `grand_stories`", true);
    
    foreach($exploded as $email){
        @list($text, $html) = explode("Content-Type: text/html;", $email);
        $paragraphs = array_values(array_filter(explode("\n", $text), function($text){ return (trim($text) != ""); }));
        foreach($allPeople as $person){
            if($person->getName() != "Eleni.Stroulia"){
                continue;
            }
            $skipNext = false;
            foreach($paragraphs as $key => $paragraph){
                if($skipNext){
                    $skipNext = false;
                    continue;
                }
                if(isset($paragraphs[$key+1]) && strstr($paragraphs[$key+1], "Full Story") !== false){
                    $paragraph .= "<br />{$paragraphs[$key+1]}";
                    $skipNext = true;
                }
                $paragraph = urldecode(preg_replace("/=(.{2})=(.{2})=(.{2})/", "%$1%$2%$3", $paragraph));
                $paragraph = preg_replace("/\[(http.*?)\]/", "<a href='$1'>$1</a>", $paragraph);
                if(strstr($paragraph, "To: {$person->getFirstName()} {$person->getLastName()}") === false && 
                  (strstr($paragraph, $person->getNameForForms()) !== false ||
                   strstr($paragraph, "{$person->getFirstName()} {$person->getLastName()}")) !== false){
                    DBFunctions::insert('grand_stories',
                                        array('user_id' => $person->getId(),
                                              'paragraph' => $paragraph));
                }
            }
        }
    }
    
?>
