<?php
include_once('../commandLine.inc');

//change the schema

$sql = "ALTER TABLE `grand_notifications` CHANGE COLUMN `user` `user_id` INT(11) NOT NULL COMMENT 'The user this request is for'";
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