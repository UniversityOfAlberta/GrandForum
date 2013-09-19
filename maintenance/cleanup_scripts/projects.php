<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_project` ADD COLUMN `parent_id` INT(11) NOT NULL DEFAULT 0 AFTER id";
DBFunctions::execSQL($sql, true);


//grand_project_members
$sql = "RENAME TABLE grand_user_projects TO grand_project_members";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_project_members` CHANGE COLUMN `user` `user_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);


// $sql = "CREATE TABLE IF NOT EXISTS `grand_product_projects` (
//   `product_id` int(11) NOT NULL,
//   `project_id` int(11) NOT NULL,
//   PRIMARY KEY (`product_id`,`project_id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

// DBFunctions::execSQL($sql, true);


echo "ALL DONE!\n";
  
?>
