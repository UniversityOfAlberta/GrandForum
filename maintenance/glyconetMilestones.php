<?php

require_once('commandLine.inc');
require_once('../Classes/PHPExcel/IOFactory.php');

$year = REPORTING_YEAR;

define('UNIVERSITY_COL',0);
define('PI_COL',        1);
define('ACTIVITY_COL',  2);
define('MILESTONE_COL', 3);
define('Y1_Q1_COL',     4);
define('Y1_Q2_COL',     5);
define('Y1_Q3_COL',     6);
define('Y1_Q4_COL',     7);
define('Y2_Q1_COL',     8);
define('Y2_Q2_COL',     9);
define('Y2_Q3_COL',     10);
define('Y2_Q4_COL',     11);
define('Y3_Q1_COL',     12);
define('Y3_Q2_COL',     13);
define('Y3_Q3_COL',     14);
define('Y3_Q4_COL',     15);
define('LEADER_COL',    16);
define('PERSON_COL',    17);
define('TBD_COL',       18);

$wgUser = User::newFromId(1);

if(isset($argv[0])){
    if(strtolower($argv[0]) == "catalyst"){
        $report = RP_CATALYST;
    }
    else if(strtolower($argv[0]) == "translational"){
        $report = RP_TRANS;
    }
    else{
        echo "Invalid Report specified\n";
        exit;
    }
}
else{
    echo "You must specify a report type\n";
    exit;
}

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
        echo "== Processing milestones for {$person->getNameForForms()} ==\n";
        $startYear = @substr($config->getValue('projectPhaseDates', PROJECT_PHASE), 0, 4);
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
                $people = array();
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
                        case Y2_Q1_COL:
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
                            break;
                        case Y3_Q1_COL:
                            $quarters[] = ($startYear+2).":1";
                            break;
                        case Y3_Q2_COL:
                            $quarters[] = ($startYear+2).":2";
                            break;
                        case Y3_Q3_COL:
                            $quarters[] = ($startYear+2).":3";
                            break;
                        case Y3_Q4_COL:
                            $quarters[] = ($startYear+2).":4";
                            break;
                        case LEADER_COL:
                            $leader = $cell;
                            break;
                        case PERSON_COL:
                            $people = explode(",", $cell);
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
                $_POST['status'] = "New";
                $_POST['people'] = $people;
                $_POST['end_date'] = ($startYear+2)."-12-31 00:00:00";
                $_POST['quarters'] = implode(",", $quarters);
                $_POST['comment'] = $comments;
                
                APIRequest::doAction('ProjectMilestone', true);
                echo "\tMilestone added\n";
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
$allPeople = Person::getAllPeople(NI);
foreach($allPeople as $person){
    $type = BLOB_EXCEL;
    $proj = 0;
    $section = CAT_MILESTONES;
    $item = CAT_MIL_UPLOAD;
    $subitem = 0;
    $blob = new ReportBlob($type, $year, $person->getId(), $proj);
    $blob_address = ReportBlob::create_address($report, $section, $item, $subitem);
    $blob->load($blob_address);
    $data = $blob->getData();
    $project = "";
    if($data != null && $data != ""){
        $data = json_decode($data);
        $data = base64_decode($data->file);
    }

    $leadership = $person->leadership();
    foreach($leadership as $lead){
        if(file_exists("docs/{$person->getName()} {$lead->getName()}.xlsx") &&
           !isset($alreadyDone[$person->getName()][$lead->getName()])){
            $data = file_get_contents("docs/{$person->getName()} {$lead->getName()}.xlsx");
            $project = $lead->getName();
            $alreadyDone[$person->getName()][$lead->getName()] = true;
            
            addMilestones($data, $person, $project);
            $data = false;
        }
    }
    if($data == ""){
        echo "No data uploaded for {$person->getNameForForms()}\n";
        continue;
    }
    if($data != false){
        addMilestones($data, $person, $project);
    }
}

?>
