<?php
    
    require_once('commandLine.inc');

    $sql = "SELECT * 
            FROM `grand_project_champions`";
    $data = DBFunctions::execSQL($sql);
    foreach($data as $row){
        $sql = "SELECT * 
                FROM `grand_project_members` 
                WHERE `user_id` = '{$row['user_id']}'
                AND `project_id` = '{$row['project_id']}'
                AND `end_date` = '0000-00-00 00:00:00'";
        $data = DBFunctions::execSQL($sql);
        if(count($data) == 0){
            $sql = "INSERT INTO `grand_project_members`
                    (`user_id`,`project_id`,`start_date`,`end_date`) VALUES
                    ('{$row['user_id']}','{$row['project_id']}','{$row['start_date']}','{$row['end_date']}')";
            DBFunctions::execSQL($sql, true);
        }
    }

?>
