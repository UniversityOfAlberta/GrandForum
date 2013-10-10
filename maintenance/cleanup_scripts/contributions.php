<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `projects`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `partner_id`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `type`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `kind`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_contributions` DROP COLUMN `cash`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_contributions` CHANGE COLUMN `date` `change_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_contributions` MODIFY COLUMN `year` YEAR NOT NULL DEFAULT 0";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";
   
?>
