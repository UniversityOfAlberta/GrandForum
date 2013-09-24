<?php
include_once('../commandLine.inc');
$sql = "RENAME TABLE `mw_list_request` TO `grand_list_request` ;";
DBFunctions::execSQL($sql, true);

$sql = "SELECT id, requesting_user, user
        FROM grand_list_request";
$data = DBFunctions::execSQL($sql);
$rUser = array();
$uUser = array();
foreach($data as $row){
    if(!is_numeric($row['requesting_user'])){
        $p = Person::newFromName($row['requesting_user']);
        if($p->getId() == ""){
            $rUser[$row['id']] = 0;
        }
        else{
            $rUser[$row['id']] = $p->getId();
        }
    }
    if(!is_numeric($row['user'])){
        $p = Person::newFromName($row['user']);
        if($p->getId() == ""){
            $uUser[$row['id']] = 0;
        }
        else{
            $uUser[$row['id']] = $p->getId();
        }
    }
}

$sql = "ALTER TABLE  `grand_list_request` CHANGE  `requesting_user`  `requesting_user` INT( 11 ) NOT NULL";
DBFunctions::execSQL($sql, true);

foreach($rUser as $id => $user){
    $sql = "UPDATE `grand_list_request`
            SET `requesting_user` = '".mysql_real_escape_string($user)."'
            WHERE `id` = '$id'";
    DBFunctions::execSQL($sql, true);
}

$sql = "ALTER TABLE  `grand_list_request` CHANGE  `user`  `user` INT( 11 ) NOT NULL";
DBFunctions::execSQL($sql, true);

foreach($uUser as $id => $user){
    $sql = "UPDATE `grand_list_request`
            SET `user` = '".mysql_real_escape_string($user)."'
            WHERE `id` = '$id'";
    DBFunctions::execSQL($sql, true);
}

$sql = "SELECT * FROM `grand_list_request`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $created = "0";
    $ignored = "0";
    if($row['created'] == 'true' || $row['created'] == '1'){
        $created = "1";
    }
    if($row['ignore'] == 'true' || $row['ignore'] == '1'){
        $ignored = "1";
    }
    $sql = "UPDATE `grand_list_request`
            SET `created` = '$created',
                `ignore` = '$ignored'
            WHERE `id` = '{$row['id']}'";
    DBFunctions::execSQL($sql, true);
}

$sql = "ALTER TABLE  `grand_list_request` CHANGE  `ignore`  `ignore` BOOLEAN NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_list_request` CHANGE  `created`  `created` BOOLEAN NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_list_request` ADD INDEX (  `created` )";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_list_request` ADD INDEX (  `ignore` )";
DBFunctions::execSQL($sql, true);

?>
