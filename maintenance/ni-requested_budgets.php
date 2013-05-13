<?php
require_once('commandLine.inc');
    

$csv = '"Names"';    
   
$allPeople = Person::getAllPeople(CNI);
$allProjects = Project::getAllProjects();

$csv .= "\n";

foreach($allPeople as $person){
    $budget = $person->getRequestedBudget(2012);
    $person_name = $person->getName();
    $csv .= '"'.$person_name.'"';

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
