<?php
require_once('commandLine.inc');
    

$csv = '"Names"';    
   
$allPeople = Person::getAllPeople(PNI);
$allProjects = Project::getAllProjects();

foreach ($allProjects as $project) {
    $csv .= ',"'.$project->getName() .'"';
}
$csv .= "\n";

foreach($allPeople as $person){

    $person_name = $person->getName();
    $csv .= '"'.$person_name.'"';

    foreach($allProjects as $project){
        $people = $project->getAllPeople();
        $conflict = 0;
        foreach($people as $p){
            if($person->getId() == $p->getId()){
                $conflict = 1;
                break;
            }
        }
        $csv .= ',"'.$conflict.'"';

    }
    $csv .= "\n";
}


echo $csv;
    

function execSQLStatement($sql, $update=false){
    if($update == false){
        $dbr = wfGetDB(DB_SLAVE);
    }
    else {
        $dbr = wfGetDB(DB_MASTER);
        return $dbr->query($sql);
    }
    $result = $dbr->query($sql);
    $rows = null;
    if($update == false){
        $rows = array();
        while ($row = $dbr->fetchRow($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

?>
