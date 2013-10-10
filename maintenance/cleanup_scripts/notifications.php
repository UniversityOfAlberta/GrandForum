<?php
include_once('../commandLine.inc');

//change the schema

$sql = "ALTER TABLE `grand_notifications` CHANGE COLUMN `user` `user_id` INT(11) NOT NULL COMMENT 'The user this request is for'";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";

?>
