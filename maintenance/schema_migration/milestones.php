<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE grand_milestones ADD product_id INT AFTER project_id";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}


echo "ALL DONE!\n";

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
?>