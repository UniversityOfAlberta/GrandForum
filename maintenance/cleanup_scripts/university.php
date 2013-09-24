<?php
include_once('../commandLine.inc');

$sql = "RENAME TABLE `mw_universities` TO `grand_universities` ;";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_universities` ADD  `order` INT NOT NULL AFTER  `university_name`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_universities` ADD  `default` BOOLEAN NOT NULL AFTER  `order`";
DBFunctions::execSQL($sql, true);

$sql = "SELECT * FROM `grand_universities`";
$data = DBFunctions::execSQL($sql);
foreach($data as $id => $row){
    $sql = "UPDATE `grand_universities`
            SET `order` = '".(($id+1)*10)."'
            WHERE university_id = {$row['university_id']}";
    DBFunctions::execSQL($sql, true);
}

$sql = "INSERT INTO `grand_universities` (`university_name`,`order`,`default`)
        VALUES ('Unknown', 0, 1)";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_user_university`
        SET `university_id` = (SELECT university_id FROM `grand_universities` WHERE `university_name` = 'Unknown')
        WHERE `university_id` = 0";
DBFunctions::execSQL($sql, true);

$sql = "RENAME TABLE `mw_user_university` TO `grand_user_university` ;";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_user_university` DROP `comment`";
DBFunctions::execSQL($sql, true);

$sql = "DROP TABLE IF EXISTS `grand_positions`";
DBFunctions::execSQL($sql, true);

$sql = "CREATE TABLE IF NOT EXISTS `grand_positions` (
  `position_id` int(11) NOT NULL AUTO_INCREMENT,
  `position` varchar(256) NOT NULL,
  `order` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL,
  PRIMARY KEY (`position_id`),
  UNIQUE KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
DBFunctions::execSQL($sql, true);

$sql = "INSERT INTO `grand_positions` (`position`,`order`,`default`) VALUES
        ('Other', 0, 1),
        ('Undergraduate', 10, 0),
        ('Masters Student', 20, 0),
        ('PhD Student', 30, 0),
        ('PostDoc', 40, 0),
        ('Professor', 50, 0),
        ('Assistant Professor', 60, 0),
        ('Associate Professor', 70, 0),
        ('Dean of Research', 80, 0),
        ('Associate Dean of Research', 90, 0),
        ('Associate Dean Student Affairs', 100, 0),
        ('Director', 110, 0),
        ('Canada Research Chair', 120, 0),
        ('Technician', 130, 0),
        ('VP Research', 140, 0)";
DBFunctions::execSQL($sql, true);

$sql = "SELECT * FROM grand_positions";
$data = DBFunctions::execSQL($sql);
$positions = array();
foreach($data as $row){
    $positions[$row['position']] = $row['position_id'];
}

$sql = "SELECT * FROM grand_user_university";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    if(isset($row['position'])){
        $pos_id = 1;
        if(isset($positions[$row['position']])){
            $pos_id = $positions[$row['position']];
        }
        if(is_numeric($row['position'])){
            $pos_id = $row['position'];
        }
        $sql = "UPDATE grand_user_university
                SET position = '{$pos_id}'
                WHERE id = {$row['id']}";
        DBFunctions::execSQL($sql, true);
    }
}

$sql = "ALTER TABLE `grand_user_university` CHANGE COLUMN `position` `position_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_user_university` ADD INDEX (  `user_id` )";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_user_university` ADD INDEX (  `university_id` )";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_user_university` ADD INDEX (  `position_id` )";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_user_university` ADD INDEX (  `end_date` )";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_user_university` ADD INDEX (  `start_date` )";
DBFunctions::execSQL($sql, true);

?>
