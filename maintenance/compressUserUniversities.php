<?php
include_once('commandLine.inc');

$sql = "SELECT * 
        FROM `grand_user_university`
        ORDER BY user_id, id";
$data = DBFunctions::execSQL($sql);

$initialId = 0;
$lastUser = "";
$lastUni = "";
$lastPos = "";
$lastDept = "";
$nDups = 0;
foreach($data as $row){
    if($lastUser == $row['user_id'] &&
       $lastUni == $row['university_id'] &&
       $lastPos == $row['position_id'] &&
       $lastDept == $row['department']){
        // Duplicate
        $sql = "DELETE FROM `grand_user_university`
                WHERE id = {$row['id']}";
        DBFunctions::execSQL($sql, true);
        $sql = "UPDATE `grand_user_university`
                SET `end_date` = '{$row['end_date']}'
                WHERE `id` = {$initialId}";
        DBFunctions::execSQL($sql, true);
        $nDups++;
    }
    else{
        // Changed
        $initialId = $row['id'];
    }   
    $lastUser = $row['user_id'];
    $lastUni = $row['university_id'];
    $lastPos = $row['position_id'];
    $lastDept = $row['department'];
}
echo $nDups." Duplicated Deleted\n";

?>
