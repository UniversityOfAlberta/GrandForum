<?php
include_once('../commandLine.inc');

$sql = "RENAME TABLE `mw_eval` TO `grand_eval` ;";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_eval` CHANGE COLUMN `eval_id` `user_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);

?>