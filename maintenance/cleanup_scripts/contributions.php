<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `projects`";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `partner_id`";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `type`";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `kind`";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `cash`";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_contributions` CHANGE COLUMN `date` `change_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$data = execSQLStatement($sql, true);

$sql = "ALTER TABLE `grand_contributions` MODIFY COLUMN `year` YEAR NOT NULL DEFAULT 0";
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