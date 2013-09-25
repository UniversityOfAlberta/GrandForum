<?php
include_once('../commandLine.inc');

$sql = "RENAME TABLE `mw_eval` TO `grand_eval` ;";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_eval` CHANGE COLUMN `eval_id` `user_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "SELECT * FROM `mw_evalpdf_index`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $type = 'Project';
    if($row['type'] == 1){
        $type = 'Researcher';
    }
    $sql = "INSERT INTO `grand_eval` (`user_id`, `sub_id`, `type`, `year`) VALUES
            ({$row['user_id']}, {$row['subject_id']}, '$type', '2010')";
    DBFunctions::execSQL($sql, true);
}

?>
