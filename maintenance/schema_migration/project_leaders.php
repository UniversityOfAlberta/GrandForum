<?php
include('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE grand_project_leaders ADD COLUMN project_id INT AFTER project_name";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

//Migrate the data
$sql = "UPDATE grand_project_leaders l, grand_project p SET l.project_id = p.id WHERE l.project_name = p.name";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE grand_project_leaders DROP COLUMN project_name";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

//---------------HELPERS
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
