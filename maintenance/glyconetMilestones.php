<?php

require_once('commandLine.inc');
require_once('../Classes/PHPExcel/IOFactory.php');

$year = 2020;

define('UNIVERSITY_COL',0);
define('PI_COL',        1);
define('ACTIVITY_COL',  2);
define('MILESTONE_COL', 3);
define('Y1_Q1_COL',     4);
define('Y1_Q2_COL',     5);
define('Y1_Q3_COL',     6);
define('Y1_Q4_COL',     7);
//define('Y2_Q1_COL',     8);
//define('Y2_Q2_COL',     9);
//define('Y2_Q3_COL',     10);
//define('Y2_Q4_COL',     11);
define('LEADER_COL',    8);
define('PERSON_COL',    9);
define('TBD_COL',       10);

$wgUser = User::newFromId(1);

function addMilestones($data, $person, $project){
    global $config;
    $tmpn = tempnam(sys_get_temp_dir(), 'XLS');
    if ($tmpn === false) {
        // Failed to reserve a temporary file.
        echo "Could not reserve temp file.";
        return false;
    }
    $tmpf = fopen($tmpn, 'w');
    if ($tmpf === false) {
        echo "Could not create temp file.";
        // TODO: log?
        unlink($tmpn);
        return false;
    }

    if (fwrite($tmpf, $data) === false) {
        // TODO: log?
        // Error writing to temporary file.
        echo "Could not write to temp file.";
        fclose($tmpf);
        unlink($tmpn);
        return false;
    }
    fclose($tmpf);
    // Process Data
    try {
        $newStructure = array();
        $objReader = PHPExcel_IOFactory::createReaderForFile($tmpn);
        $class = get_class($objReader);
        if($class != "PHPExcel_Reader_Excel5" && $class != "PHPExcel_Reader_Excel2007"){
            return false;
        }
        $objReader->setReadDataOnly(true);
        $obj = $objReader->load($tmpn);
        $obj->setActiveSheetIndex(0);
        $cells = $obj->getActiveSheet()->toArray();
        $activity = "";
        echo "== Processing milestones for {$person->getNameForForms()} - {$project} ==\n";
        $startYear = 2020;
        foreach($cells as $rowN => $row){
            if($rowN == 0){
                foreach($row as $colN => $cell){
                    $cell = trim($cell);
                    if($colN == 1){
                        if($project == ""){
                            $project = $cell;
                        }
                    }
                }
            }
            if($rowN >= 7){
                $title = "";
                $leader = "";
                $quarters = array();
                $people = "";
                $comments = "";
                foreach($row as $colN => $cell){
                    $cell = trim($cell);
                    if($cell == ""){
                        continue;
                    }
                    switch($colN){
                        case UNIVERSITY_COL:
                            break;
                        case PI_COL:
                            break;
                        case ACTIVITY_COL:
                            $activity = $cell;
                            break;
                        case MILESTONE_COL:
                            $title = $cell;
                            break;
                        case Y1_Q1_COL:
                            $quarters[] = ($startYear).":1";
                            break;
                        case Y1_Q2_COL:
                            $quarters[] = ($startYear).":2";
                            break;
                        case Y1_Q3_COL:
                            $quarters[] = ($startYear).":3";
                            break;
                        case Y1_Q4_COL:
                            $quarters[] = ($startYear).":4";
                            break;
                        /*case Y2_Q1_COL:
                            $quarters[] = ($startYear+1).":1";
                            break;
                        case Y2_Q2_COL:
                            $quarters[] = ($startYear+1).":2";
                            break;
                        case Y2_Q3_COL:
                            $quarters[] = ($startYear+1).":3";
                            break;
                        case Y2_Q4_COL:
                            $quarters[] = ($startYear+1).":4";
                            break;*/
                        case LEADER_COL:
                            $leader = $cell;
                            break;
                        case PERSON_COL:
                            $people = $cell;
                            break;
                        case TBD_COL:
                            $comments = $cell;
                            break;
                    }
                }
                $p = Project::newFromName($project);
                if($p == null){
                    $p = Project::newFromTitle($project);
                }
                if($p == null){
                    echo "\tProject not valid\n";
                    break;
                }
                if(trim($title) == ""){
                    continue;
                }
                // Insert
                $_POST['user_name'] = $person->getName();
                $_POST['project'] = $p->getName();
                $_POST['leader'] = $leader;
                $_POST['activity'] = $activity;
                $_POST['milestone'] = $title;
                $_POST['title'] = $title;
                $_POST['new_title'] = $title;
                $_POST['problem'] = "";
                $_POST['description'] = "";
                $_POST['assessment'] = "";
                $_POST['modification'] = "";
                $_POST['status'] = "New";
                $_POST['people'] = $people;
                $_POST['end_date'] = ($startYear+1)."-12-31 00:00:00";
                $_POST['quarters'] = implode(",", $quarters);
                $_POST['comment'] = $comments;
                
                APIRequest::doAction('ProjectMilestone', true);
                echo "\tMilestone added $title\n";
            }
        }
    }
    catch (Exception $e) {
        // File is probably encrypted
        echo "Error reading Milestones for {$project->getNameForForms()}\n";
    }
    
    // Delete tmp file
    unlink($tmpn);
}

$alreadyDone = array();
$projects = Project::getAllProjects();
foreach($projects as $project){
    $fileName = "docs/{$project->getName()}.xls";
    if(file_exists($fileName)){
        $data = file_get_contents($fileName);
        $person = array_pop($project->getLeaders());
        addMilestones($data, $person, $project->getName());
    }
    else {
        echo "No data uploaded for {$project->getName()}\n";
        continue;
    }
}

?>
