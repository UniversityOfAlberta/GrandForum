<?php

require_once('commandLine.inc');
global $wgUser;

function fixAuthors($pub, $authors){
    $pub->authors = array();
    foreach($authors as $author){
        if($author->author->orcid != ""){
            $p = Person::newFromOrcid($author->author->orcid);
        }
        else{
            $p = new LimitedPerson(array());
        }
        
        if($p->getId() != 0){
            // ORCID located
            $pub->authors[] = $p;
        }
        else{
            // Simple author matching
            $obj = new stdClass;
            $found = false;
            foreach($author->institutions as $institution){
                if($institution->display_name == "University of Alberta" || 
                   strstr($institution->display_name, 'Alberta') !== false || 
                   strstr($institution->display_name, 'Edmonton') !== false){
                    $found = true;
                }
            }
            if($found){
                // At UofA (or probably anyways)
                $obj->name = trim($author->author->display_name);
            }
            else{
                // Not at UofA
                $names = explode(" ", trim($author->author->display_name), 2);
                if(count($names) > 1){
                    $obj->name = "\"{$names[1]}, {$names[0]}\"";
                }
                else{
                    $obj->name = $names[0];
                }
            }
            $pub->authors[] = $obj;
        }
    }
    return $pub;
}

$wgUser = User::newFromId(1);

$people = array_merge(Person::getAllPeople("Faculty"), Person::getAllPeople("ATS"));

$alreadyImported = array();

foreach($people as $person){
    $orcid = $person->getOrcid();
    if($orcid != ""){
        echo $person->getName()." ... \n";
        $alex = json_decode(file_get_contents("https://api.openalex.org/works?filter=author.orcid:{$orcid},from_publication_date:".REPORTING_CYCLE_START.
                                              "&sort=publication_year:desc&per-page=100&mailto=dwt@ualberta.ca"));
        foreach($alex->results as $result){
            $doi = trim($result->doi);
            $title = trim($result->title);
            $authors = $result->authorships;
            if($doi != "" && !isset($alreadyImported[$doi])){
                $alreadyImported[$doi] = true;
                echo "\t{$doi} ... ";
                $res = array('created' => array());
                $pubByTitle = Product::newFromTitle($title);
                $pubByDoi = Product::newFromBibTeXId(str_replace("https://doi.org/", "", $doi));
                if(!$pubByTitle->exists() &&
                   !$pubByDoi->exists()){
                    $_POST['doi'] = $doi;
                    $api = new ImportDOIAPI();
                    $res = $api->doAction();
                }
                if(!empty($res['created'])){
                    $id = $res['created'][0]['id'];
                    DBFunctions::update('grand_products',
                                        array('created_by' => $person->getId()),
                                        array('id' => $id));
                    $pub = Product::newFromId($id);
                    $pub->access_id = 0;
                    $pub->acceptance_date = $result->publication_date;
                    $pub->date = $result->publication_date;
                    $description = array();
                    if(isset($result->abstract_inverted_index)){
                        foreach($result->abstract_inverted_index as $word => $indices){
                            foreach($indices as $index){
                                $description[$index] = $word;
                            }
                        }
                    }
                    ksort($description);
                    $pub->description = html_entity_decode(implode(" ", $description));
                    $pub = fixAuthors($pub, $authors);
                    $pub->update(true);
                    echo "created\n";
                }
                else if($pubByDoi->exists() && abs(strtotime($pubByDoi->dateCreated) - strtotime($pubByDoi->lastModified)) <= 10){
                    $pubByDoi = fixAuthors($pubByDoi, $authors);
                    $pubByDoi->update(true);
                    DBFunctions::update('grand_products',
                                        array('date_changed' => $pubByDoi->lastModified),
                                        array('id' => $pubByDoi->getId()));
                    echo "updating authors\n";
                }
                else{
                    echo "skip\n";
                }
            }
        }
    }
    
}
   
?>
