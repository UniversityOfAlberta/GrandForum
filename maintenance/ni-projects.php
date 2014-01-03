<?php
require_once('commandLine.inc');

$csv = '"Names"';    
   
$cnistmp = Person::getAllPeopleDuring(CNI, (REPORTING_YEAR+1).REPORTING_NCE_START_MONTH, (REPORTING_YEAR+2).REPORTING_NCE_END_MONTH);
$cnis = array();
foreach($cnistmp as $cni){
    $leadership = $cni->leadership();
    foreach($leadership as $lead){
        if($lead->getPhase() == 2 && !$lead->isSubProject()){
            $cnis[] = $cni;
            break;
        }
    }
}
$pnis = Person::getAllPeopleDuring(PNI, (REPORTING_YEAR+1).REPORTING_NCE_START_MONTH, (REPORTING_YEAR+2).REPORTING_NCE_END_MONTH);
$allPeople = array_merge($pnis, $cnis);
$allProjects = Project::getAllProjects();
foreach($allProjects as $key => $project){
    if($project->getPhase() != PROJECT_PHASE || $project->isSubProject()){
        unset($allProjects[$key]);
    }
}

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

?>
