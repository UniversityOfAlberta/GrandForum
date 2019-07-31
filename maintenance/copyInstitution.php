<?php

require_once('commandLine.inc');

function addUserUniversity($name, $uni, $dept, $pos){
    $person = Person::newFromName($name);
    $_POST['university'] = $uni;
    $_POST['department'] = $dept;
    $_POST['position'] = $pos;
    $_POST['startDate'] = '2010-01-01 00:00:00';
    $_POST['endDate'] = '0000-00-00 00:00:00';
    $api = new PersonUniversitiesAPI();
    $api->params['id'] = $person->getId();
    $api->doPOST();
}

$wgUser = User::newFromId(1);

$people = Person::getAllPeople('all');
foreach($people as $person){
    $projects = $person->getProjects();
    $unis = $person->getUniversities();
    if(count($unis) == 0 && count($projects) > 0){
        $project = $projects[0];
        echo "COPY: {$person->getName()} ({$project->getName()}/{$project->getFullName()})\n";
        addUserUniversity($person->getName(), $project->getName(), $project->getFullName(), "Unknown");
    }
}

?>
