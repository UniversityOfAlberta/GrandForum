<?php
include_once('../commandLine.inc');

$sql = "ALTER TABLE  `grand_roles` CHANGE  `user` `user_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);

?>
