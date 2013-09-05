<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_movedOn` CHANGE COLUMN `date` `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_movedOn` ADD COLUMN `date_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
$data = execSQLStatement($sql, true);

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