<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_project` ADD COLUMN `parent_id` INT(11) NOT NULL DEFAULT 0 AFTER id";
DBFunctions::execSQL($sql, true);


//grand_project_descriptions
$sql = "ALTER TABLE `grand_project_descriptions` ADD COLUMN `problem` TEXT";
DBFunctions::execSQL($sql, true);
$sql = "ALTER TABLE `grand_project_descriptions` ADD COLUMN `solution` TEXT";
DBFunctions::execSQL($sql, true);

// //grand_project_members
$sql = "RENAME TABLE grand_user_projects TO grand_project_members";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_project_members` CHANGE COLUMN `user` `user_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);


$sql = "CREATE TABLE IF NOT EXISTS `grand_challenges`(
	   `id` INT(11) NOT NULL AUTO_INCREMENT,
	   `name` VARCHAR(255) NOT NULL,
	   PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
DBFunctions::execSQL($sql, true);	

$sql = "INSERT INTO `grand_challenges`(`name`) VALUES
		('Entertainment'),
		('Learning'),
		('Healthcare'),
		('Sustainability'),
		('Big Data'),
		('Work'),
		('Citizenship')";
DBFunctions::execSQL($sql, true);	

$sql = "CREATE TABLE IF NOT EXISTS `grand_project_challenges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`),
   KEY `project_id` (`project_id`),
   KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

DBFunctions::execSQL($sql, true);

////Leader table
$sql = "ALTER TABLE `grand_project_leaders` ADD COLUMN `type` ENUM('leader','co-leader','manager') NOT NULL AFTER `project_id`";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_project_leaders` SET `type`='leader' WHERE `co_lead`='False' AND `manager`='0'";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_project_leaders` SET `type`='co-leader' WHERE `co_lead`='True'";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_project_leaders` SET `type`='manager' WHERE `manager`='1'";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_project_leaders` DROP COLUMN `co_lead`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_project_leaders` DROP COLUMN `manager`";
DBFunctions::execSQL($sql, true);

//grand_project_champions
$sql = "CREATE TABLE IF NOT EXISTS `grand_project_champions` (
  `project_id` int(11) unsigned NOT NULL,
  `champion` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`project_id`,`champion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";
  
?>
