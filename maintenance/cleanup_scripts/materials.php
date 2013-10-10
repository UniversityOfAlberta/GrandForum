<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_materials` DROP KEY `timestamp`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_materials` CHANGE COLUMN `timestamp` `change_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";
 
?>
