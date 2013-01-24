<?php

$fundedProjects = array();
$contents = explode("\n", file_get_contents("fundedProjects.csv"));
$allProjects = array();
$greeceProjects = array();
foreach($contents as $line){
    $csv = str_getcsv($line, ',', '"');
    $projectName = trim($csv[3]);
    $money = trim($csv[6]);
    if($projectName != ""){
        $allProjects[$projectName] = $money;
    }
}

$contents = explode("\n", file_get_contents("greece.csv"));
foreach($contents as $line){
    $csv = str_getcsv($line, ',', '"');
    $projectName = trim($csv[12]);
    if($projectName != "" && isset($allProjects[$projectName])){
        $greeceProjects[$projectName] = $allProjects[$projectName];
    }
}

asort($greeceProjects);
$greeceProjects = array_reverse($greeceProjects);

$categories = array();
$funding = array();
$sum = 0;
echo count($greeceProjects);
foreach($greeceProjects as $project => $value){
    $categories[] = $project;
    $funding[] = round(intval($value)/1000000, 2);
    $sum += intval($value)/1000000;
    $pareto[] = round($sum, 2);
}

?>
