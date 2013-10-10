<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_movedOn` CHANGE COLUMN `date` `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_movedOn` ADD COLUMN `date_changed` TIMESTAMP NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_movedOn`
        SET `date_changed` = `date_created`";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";   
?>
