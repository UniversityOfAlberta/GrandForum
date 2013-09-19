<?php
include_once('../commandLine.inc');

//change the schema
$sql = "RENAME TABLE `mw_an_poll` TO `grand_poll` ;";
DBFunctions::execSQL($sql, true);

$sql = "RENAME TABLE `mw_an_poll_collection` TO `grand_poll_collection` ;";
DBFunctions::execSQL($sql, true);

$sql = "RENAME TABLE `mw_an_poll_groups` TO `grand_poll_groups` ;";
DBFunctions::execSQL($sql, true);

$sql = "RENAME TABLE `mw_an_poll_options` TO `grand_poll_options` ;";
DBFunctions::execSQL($sql, true);

$sql = "RENAME TABLE `mw_an_poll_votes` TO `grand_poll_votes` ;";
DBFunctions::execSQL($sql, true);

$sql = "SELECT * FROM `grand_poll_collection`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $self = "0";
    if($row['self_vote'] == 'true' || $row['self_vote'] == '1'){
        $self = "1";
    }
    $sql = "UPDATE `grand_poll_collection`
            SET `self_vote` = '$self'
            WHERE `collection_id` = '{$row['collection_id']}'";
    DBFunctions::execSQL($sql, true);
}

$sql = "ALTER TABLE  `grand_poll_collection` CHANGE  `self_vote`  `self_vote` BOOLEAN NOT NULL";
DBFunctions::execSQL($sql, true);

$sql= "ALTER TABLE `grand_poll_votes` DROP `frozen`";
DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";
  
?>
