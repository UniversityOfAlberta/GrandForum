<?php

require_once('commandLine.inc');
global $wgUser;

function fixAuthors($pub, $authors){
    $pub->authors = array();
    foreach($authors as $author){
        $p = new Person(array());
        if($author->author->orcid != ""){
            $p = Person::newFromOrcid($author->author->orcid);
        }
        
        if($p instanceof Person && $p->getId() != 0){
            // ORCID located
            $pub->authors[] = $p;
        }
        else{
            // Simple author matching
            $obj = new stdClass;
            $obj->name = trim($author->author->display_name);
            $pub->authors[] = $obj;
        }
    }
    return $pub;
}

$wgUser = User::newFromId(1);

$people = Person::getAllPeople(NI);

$alreadyImported = array();

foreach($people as $person){
    $orcid = $person->getOrcid();
    if($orcid != ""){
        echo $person->getName()." ... \n";
        $query = "orcid:{$orcid}";
        $date = date('Y-m-d');
        foreach($person->getRoles(true) as $role){
            $startDate = $role->getStartDate();
            if($startDate < $date){
                $date = substr($startDate,0, 10);
            }
        }
        $alex = json_decode(file_get_contents("https://api.openalex.org/works?filter=author.{$query},from_publication_date:$date".
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
                    $_POST['project'] = "";
                    $api = new ImportDOIAPI();
                    $res = $api->doAction();
                }
                if(count($res['created']) > 0){
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
