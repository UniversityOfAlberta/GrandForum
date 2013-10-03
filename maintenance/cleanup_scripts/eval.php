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
    $sql = "SELECT * FROM `grand_eval`
            WHERE `user_id` = {$row['user_id']}
            AND `sub_id` = {$row['subject_id']}
            AND `type` = '$type'
            AND `year` = '2010'";
    $data = DBFunctions::execSQL($sql);
    if(count($data) == 0){
        $sql = "INSERT INTO `grand_eval` (`user_id`, `sub_id`, `type`, `year`) VALUES
                ({$row['user_id']}, {$row['subject_id']}, '$type', '2010')";
        DBFunctions::execSQL($sql, true);
    }
}

?>
