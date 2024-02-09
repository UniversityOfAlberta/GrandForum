<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $start = (YEAR-1)."-07-01";
    $end = (YEAR)."-07-01";
    
    $allPeople = array_merge(Person::getAllPeopleDuring(NI, $start, $end),
                             Person::getAllPeopleDuring("ATS", $start, $end));
    $json = file_get_contents("https://www.ualberta.ca/api/coveo/search/ualberta-news-feed?category=('Science%20and%20Technology')&news_source=('folio')&count=50");
    $obj = json_decode($json);
    $results = array_reverse($obj->data->results);
    foreach($allPeople as $person){
        foreach($results as $news){
            $title = $news->title;
            $excerpt = $news->excerpt;
            $description = $news->raw->ua__description;
            $keywords = $news->raw->ua__keywords;
            $date = $news->raw->ua__news_date;
            $url = $news->uri;
            
            $paragraph = "$title $excerpt $description $keywords";
            $text = "{$title}<br />{$description}<br /><a href='{$url}' target='_blank'>Full Story</a>";
            if(strstr($paragraph, $person->getNameForForms()) !== false ||
               strstr($paragraph, "{$person->getFirstName()} {$person->getLastName()}") !== false){
                $data = DBFunctions::select(array('grand_stories'),
                                            array('*'),
                                            array('user_id' => $person->getId(),
                                                  'paragraph' => $text)); 
                if(count($data) == 0){
                    echo "FOUND {$person->getName()}\n";
                    DBFunctions::insert('grand_stories',
                                        array('user_id' => $person->getId(),
                                              'paragraph' => $text));
                }
            }
        }
    }
    
?>
