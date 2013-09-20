<?php
include_once('../commandLine.inc');

//Create the common conflict table
$sql = "CREATE TABLE IF NOT EXISTS `grand_eval_conflicts` (
  `eval_id` INT(11) NOT NULL,
  `sub_id` int(11) NOT NULL,
  `type` enum('NI','PROJECT','LOI') NOT NULL,
  `year` YEAR NOT NULL,
  `conflict` tinyint(1) NOT NULL DEFAULT '0',
  `user_conflict` tinyint(1) NOT NULL DEFAULT '0',
  `preference` tinyint(4) NOT NULL DEFAULT '0',
   PRIMARY KEY (`eval_id`,`sub_id`, `type`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

DBFunctions::execSQL($sql, true);

//Migrate grand_reviewer_conflicts
$sql = "INSERT INTO `grand_eval_conflicts`(`eval_id`, `sub_id`, `type`, `year`, `conflict`, `user_conflict`) 
		SELECT `reviewer_id`, `reviewee_id`, 'NI', 2013, `conflict`, `user_conflict` FROM  `grand_reviewer_conflicts`";
DBFunctions::execSQL($sql, true);

//Migrate grand_project_conflicts
$sql = "INSERT INTO `grand_eval_conflicts`(`eval_id`, `sub_id`, `type`, `year`, `conflict`, `user_conflict`) 
		SELECT `reviewer_id`, `project_id`, 'PROJECT', 2013, `conflict`, `user_conflict` FROM  `grand_project_conflicts`";
DBFunctions::execSQL($sql, true);

//Migrate grand_loi_conflicts
$sql = "INSERT INTO `grand_eval_conflicts`(`eval_id`, `sub_id`, `type`, `year`, `user_conflict`, `preference`) 
		SELECT `reviewer_id`, `loi_id`, 'LOI', 2013, `conflict`, `preference` FROM  `grand_loi_conflicts`";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";

?>