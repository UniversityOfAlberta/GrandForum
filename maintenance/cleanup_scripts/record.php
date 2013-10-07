<?php
include_once('../commandLine.inc');

$sql = "ALTER TABLE  `grand_recorded_images` CHANGE  `person`  `user_id` INT( 11 ) NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_recordings` CHANGE  `person`  `user_id` INT( 11 ) NOT NULL";
DBFunctions::execSQL($sql, true);
  
?>
