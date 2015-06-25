<?php

require_once('commandLine.inc');
require_once('../Classes/PHPExcel/IOFactory.php');

function addAllocation($year, $amount, $ni, $project){
    DBFunctions::delete("grand_allocations",
                        array('user_id' => EQ($ni->getId()),
                              'project_id' => EQ($project->getId()),
                              'year' => EQ($year)));
    DBFunctions::insert("grand_allocations",
                        array('user_id' => $ni->getId(),
                              'project_id' => $project->getId(),
                              'year' => $year,
                              'amount' => $amount));
    echo "\tAllocation added for {$ni->getName()}:{$project->getName()} ".($year)."\n";
}

$year = REPORTING_YEAR;

define('PROJ_ROW',  0);
define('NAME_ROW',  1);
define('TOTAL_ROW', 33);

define('NAME_COL',  1);
define('YEAR1_COL', 1);
define('YEAR2_COL', 2);
define('YEAR3_COL', 3);
define('TOTAL_COL', 4);

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

$allPeople = Person::getAllPeople(NI);
foreach($allPeople as $person){
    $type = BLOB_EXCEL;
    $proj = 0;
    $section = CAT_BUDGET;
    $item = CAT_BUD_UPLOAD;
    $subitem = 0;
    $blob = new ReportBlob($type, $year, $person->getId(), $proj);
    $blob_address = ReportBlob::create_address($report, $section, $item, $subitem);
    $blob->load($blob_address);
    $data = $blob->getData();
    if($data == null || $data == ""){
        echo "No data uploaded for {$person->getNameForForms()}\n";
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
        $sheets = $obj->getAllSheets();
        for($i=1; $i<count($sheets); $i++){
            $obj->setActiveSheetIndex($i);
            $cells = $obj->getActiveSheet()->toArray();
            $project = "";
            $ni = "";
            $allocation1 = "";
            $allocation2 = "";
            $allocation3 = "";
            echo "== Processing budget for {$person->getNameForForms()}: NP-$i ==\n";
            foreach($cells as $rowN => $row){
                foreach($row as $colN => $cell){
                    $cell = trim($cell);
                    if($rowN == PROJ_ROW){
                        if($colN == NAME_COL){
                            $project = $cell;
                        }
                    }
                    else if($rowN == NAME_ROW){
                        if($colN == NAME_COL){
                            $ni = $cell;
                        }
                    }
                    else if($rowN == TOTAL_ROW){
                        if($colN == YEAR1_COL){
                            $allocation1 = $cell;
                        }
                        else if($colN == YEAR2_COL){
                            $allocation2 = $cell;
                        }
                        else if($colN == YEAR3_COL){
                            $allocation3 = $cell;
                        }
                        else if($colN == TOTAL_COL){
                            
                        }
                    }
                    else{
                        break;
                    }
                }
            }
            if($project != "" && $ni != "" &&
               $project != "NAME" && $ni != "NAME"){
                $valid = true;
                $ni = Person::newFromNameLike($ni);
                if($ni == null || $ni->getName() == ""){
                    echo "\tNI Name not valid\n";
                    $valid = false;
                }
                $p = Project::newFromName($project);
                if($p == null){
                    $p = Project::newFromTitle($project);
                }
                if($p == null){
                    echo "\tProject not valid\n";
                    $valid = false;
                }
                if($valid && $allocation1 != "" && $allocation1 != 0){
                    addAllocation($year+1, $allocation1, $ni, $p);
                }
                if($valid && $allocation2 != "" && $allocation2 != 0){
                    addAllocation($year+2, $allocation2, $ni, $p);
                }
                if($valid && $allocation3 != "" && $allocation3 != 0){
                    addAllocation($year+3, $allocation3, $ni, $p);
                }
            }
        }
    }
    catch (Exception $e) {
        // File is probably encrypted
        echo "Error reading Budget\n";
    }
    
    // Delete tmp file
    unlink($tmpn);
    
}

?>
