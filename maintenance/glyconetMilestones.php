<?php

require_once('commandLine.inc');
require_once('../Classes/PHPExcel/IOFactory.php');

$year = REPORTING_YEAR;

define('UNIVERSITY_COL', 0);
define('PI_COL', 1);
define('ACTIVITY_COL', 2);
define('MILESTONE_COL', 3);
define('Y1_Q1_COL', 4);
define('Y1_Q2_COL', 5);
define('Y1_Q3_COL', 6);
define('Y1_Q4_COL', 7);
define('Y2_Q1_COL', 8);
define('Y2_Q2_COL', 9);
define('Y2_Q3_COL', 10);
define('Y2_Q4_COL', 11);
define('Y3_Q1_COL', 12);
define('Y3_Q2_COL', 13);
define('Y3_Q3_COL', 14);
define('Y3_Q4_COL', 15);
define('PERSON_COL', 16);

$projects = Project::getAllProjects();
foreach($projects as $project){
    $type = BLOB_EXCEL;
    $person = 0;
    $proj = $project->getId();
    $section = PROP_MILESTONES;
    $report = RP_PROJECT_PROPOSAL;
    $item = PROP_MIL_UPLOAD;
    $subitem = 0;
    $blob = new ReportBlob($type, $year, $person, $proj);
    $blob_address = ReportBlob::create_address($report, $section, $item, $subitem);
    $blob->load($blob_address);
    $data = $blob->getData();
    if($data != null && $data != ""){
        $data = json_decode($data);
        $data = base64_decode($data->file);
    }
    else{
        echo "No data uploaded for {$project->getName()}\n";
        continue;
    }
    
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
        echo "== Processing milestones for {$project->getName()} ==\n";
        foreach($cells as $rowN => $row){
            if($rowN >= 7){
                $title = "";
                $quarters = array();
                $people = array();
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
                            $quarters[] = ($year).":1";
                            break;
                        case Y1_Q2_COL:
                            $quarters[] = ($year).":2";
                            break;
                        case Y1_Q3_COL:
                            $quarters[] = ($year).":3";
                            break;
                        case Y1_Q4_COL:
                            $quarters[] = ($year).":4";
                            break;
                        case Y2_Q1_COL:
                            $quarters[] = ($year+1).":1";
                            break;
                        case Y2_Q2_COL:
                            $quarters[] = ($year+1).":2";
                            break;
                        case Y2_Q3_COL:
                            $quarters[] = ($year+1).":3";
                            break;
                        case Y2_Q4_COL:
                            $quarters[] = ($year+1).":4";
                            break;
                        case Y3_Q1_COL:
                            $quarters[] = ($year+2).":1";
                            break;
                        case Y3_Q2_COL:
                            $quarters[] = ($year+2).":2";
                            break;
                        case Y3_Q3_COL:
                            $quarters[] = ($year+2).":3";
                            break;
                        case Y3_Q4_COL:
                            $quarters[] = ($year+2).":4";
                            break;
                        case PERSON_COL:
                            $people = explode(",", $cell);
                            break;
                    }
                }
                // Insert
                $_POST['project'] = $project->getName();
                $_POST['activity'] = $activity;
                $_POST['milestone'] = $title;
                $_POST['problem'] = "";
                $_POST['description'] = "";
                $_POST['assessment'] = "";
                $_POST['status'] = "New";
                $_POST['people'] = implode(",", $people);
                $_POST['end_date'] = ($year+2)."-12-31 00:00:00";
                
                APIRequest::doAction('addProjectMilestone');
                echo "\tMilestone added\n";
            }
        }
    }
    catch (Exception $e) {
        // File is probably encrypted
        echo "Error reading Milestones for {$project->getName()}\n";
    }
    
    // Delete tmp file
    unlink($tmpn);
    
}

?>
