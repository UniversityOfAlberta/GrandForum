<?php

require_once('../commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}

//MiscPresentations();
//JournalPaperPublications();
PHDThesisPublications();
PosterPublications();
ProceedingsPaperPublications();

function MiscPresentations(){

    $papers = Paper::getAllPapers('all', 'Presentation', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( preg_match("/Misc/", $type) ){    
            $data_changed = false;
            $data = $paper->getData();

            if( isset($data['conference']) && !isset($data['event_title'])) {
                $conference = $data['conference'];
                unset($data['conference']);
                $data['event_title'] = $conference;
                $data_changed = true;
            }
        

            if( isset($data['location']) && !isset($data['event_location']) ) {
                $location = $data['location'];
                unset($data['location']);
                $data['event_location'] = $location;
                $data_changed = true;
            }

            if($data_changed){
                $new_data = serialize($data);

                $sql = "UPDATE grand_products
                        SET data = '{$new_data}'
                        WHERE id = {$id}";
                
                DBFunctions::execSQL($sql, true);
                echo "$id \n";

                $i++;
            }
        }
        else{
            continue;
        }

    }

    echo "Total Presentations Changed = $i \n\n";
}

//Not Completed
function BookPublications(){

    $papers = Paper::getAllPapers('all', 'Publication', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( $type == "Book" ){    
            $data_changed = false;
            $data = $paper->getData();

            if( isset($data['address']) && !isset($data['event_title'])) {
                $conference = $data['conference'];
                unset($data['conference']);
                $data['event_title'] = $conference;
                $data_changed = true;
            }
        

            if( isset($data['location']) && !isset($data['event_location']) ) {
                $location = $data['location'];
                unset($data['location']);
                $data['event_location'] = $location;
                $data_changed = true;
            }

            if($data_changed){
                $new_data = serialize($data);

                $sql = "UPDATE grand_products
                        SET data = '{$new_data}'
                        WHERE id = {$id}";
                
                DBFunctions::execSQL($sql, true);
                echo "$id \n";

                $i++;
            }
        }
        else{
            continue;
        }

    }

    echo "Total Book Publications Changed = $i \n\n";
}

function JournalPaperPublications(){

    $papers = Paper::getAllPapers('all', 'Publication', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( $type == "Journal Paper" ){    
            $data_changed = false;
            $data = $paper->getData();

            if( isset($data['journal_title']) && !isset($data['published_in'])) {
                $published_in = $data['journal_title'];
                unset($data['journal_title']);
                $data['published_in'] = $published_in;
                $data_changed = true;
            }
        

            if( isset($data['address']) && !isset($data['url']) ) {
                $url = $data['address'];
                unset($data['address']);
                $data['url'] = $url;
                $data_changed = true;
            }

            if($data_changed){
                $new_data = serialize($data);

                $sql = "UPDATE grand_products
                        SET data = '{$new_data}'
                        WHERE id = {$id}";
                
                DBFunctions::execSQL($sql, true);
                echo "$id \n";

                $i++;
            }
        }
        else{
            continue;
        }

    }

    echo "Total Journal Paper Publications Changed = $i \n\n";
}

function PHDThesisPublications(){

    $papers = Paper::getAllPapers('all', 'Publication', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( $type == "PHD Thesis" ){    
            $data_changed = false;
            $data = $paper->getData();

            if( isset($data['school']) && !isset($data['university'])) {
                $uni = $data['school'];
                unset($data['school']);
                $data['university'] = $uni;
                $data_changed = true;
            }
        

            if( isset($data['address']) && !isset($data['url']) ) {
                $url = $data['address'];
                unset($data['address']);
                $data['url'] = $url;
                $data_changed = true;
            }

            if($data_changed){
                $new_data = serialize($data);

                $sql = "UPDATE grand_products
                        SET data = '{$new_data}'
                        WHERE id = {$id}";
                
                DBFunctions::execSQL($sql, true);
                echo "$id \n";

                $i++;
            }
        }
        else{
            continue;
        }

    }

    echo "Total PHD Thesis Publications Changed = $i \n\n";
}

function PosterPublications(){

    $papers = Paper::getAllPapers('all', 'Publication', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( $type == "Poster" ){    
            $data_changed = false;
            $data = $paper->getData();

            if( isset($data['book_title']) && !isset($data['event_title'])) {
                $title = $data['book_title'];
                unset($data['book_title']);
                $data['event_title'] = $title;
                $data_changed = true;
            }
        

            if( isset($data['address']) && !isset($data['event_location']) ) {
                $loc = $data['address'];
                unset($data['address']);
                $data['event_location'] = $loc;
                $data_changed = true;
            }

            if( isset($data['DOI']) && !isset($data['doi']) ) {
                $doi = $data['DOI'];
                unset($data['DOI']);
                $data['doi'] = $doi;
                $data_changed = true;
            }

            if($data_changed){
                $new_data = serialize($data);

                $sql = "UPDATE grand_products
                        SET data = '{$new_data}'
                        WHERE id = {$id}";
                
                DBFunctions::execSQL($sql, true);
                echo "$id \n";

                $i++;
            }
        }
        else{
            continue;
        }

    }

    echo "Total Poster Publications Changed = $i \n\n";
}

function ProceedingsPaperPublications(){

    $papers = Paper::getAllPapers('all', 'Publication', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( $type == "Proceedings Paper" ){    
            $data_changed = false;
            $data = $paper->getData();

            if( isset($data['book_title']) && !isset($data['event_title'])) {
                $title = $data['book_title'];
                unset($data['book_title']);
                $data['event_title'] = $title;
                $data_changed = true;
            }
        

            if( isset($data['address']) && !isset($data['event_location']) ) {
                $loc = $data['address'];
                unset($data['address']);
                $data['event_location'] = $loc;
                $data_changed = true;
            }


            if($data_changed){
                $new_data = serialize($data);

                $sql = "UPDATE grand_products
                        SET data = '{$new_data}'
                        WHERE id = {$id}";
                
                DBFunctions::execSQL($sql, true);
                echo "$id \n";

                $i++;
            }
        }
        else{
            continue;
        }

    }

    echo "Total Proceedings Paper Publications Changed = $i \n\n";
}