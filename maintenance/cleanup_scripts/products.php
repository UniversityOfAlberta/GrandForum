<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_products` CHANGE COLUMN `last_modified` `date_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_products` ADD COLUMN `date_created` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_products` SET `date_created`=`date_changed`";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";
  
?>
