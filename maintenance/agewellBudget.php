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

define("NAME_COL", 3);
define("NAME_ROW", 2);

define("TOTAL_COL", 7);
define("TOTAL_ROW", 21);

$wgUser = User::newFromId(1);

$allProjects = Project::getAllProjects();
foreach($allProjects as $project){
    if(!file_exists($project->getName().".xlsx")){
        echo "Missing file '{$project->getName()}.xlsx'\n";
        continue;
    }
    $data = file_get_contents($project->getName().".xlsx");
    $type = BLOB_EXCEL;
    $proj = 0;
    $report = RP_LEADER;
    $section = LDR_BUDGET;
    $item = LDR_BUD_UPLOAD;
    $subitem = 0;
    $blob = new ReportBlob($type, $year, 0, $proj);
    $blob_address = ReportBlob::create_address($report, $section, $item, $subitem);
    $blob->store($data, $blob_address);
    if($data == null || $data == ""){
        echo "No data uploaded for {$project->getNameForForms()}\n";
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
        echo "== Processing budget for {$project->getName()} ==\n";
        for($i=1; $i<count($sheets); $i++){
            $obj->setActiveSheetIndex($i);
            $cells = $obj->getActiveSheet()->toArray();
            $name = "";
            $allocation = "";
            foreach($cells as $rowN => $row){
                foreach($row as $colN => $cell){
                    if($rowN == NAME_ROW && $colN == NAME_COL){
                        $name = $cell;
                        
                    }
                    else if($rowN == TOTAL_ROW && $colN == TOTAL_COL){
                        $allocation = $cell;
                    }
                }
            }
            if($name != ""){
                $valid = true;
                $ni = Person::newFromNameLike($name);
                if($ni == null || $ni->getName() == ""){
                    $ni = Person::newFromReversedName($name);
                }
                if($ni == null || $ni->getName() == ""){
                    echo "\tNI Name not valid\n";
                    $valid = false;
                }
                if($valid && $allocation != "" && $allocation != 0){
                    addAllocation($year, $allocation, $ni, $project);
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
