<?php
    
    require_once('commandLine.inc');
    
    $data = DBFunctions::select(array('grand_project_leaders'),
                                array('*'));
    foreach($data as $row){
        DBFunctions::insert('grand_roles',
                            array('user_id' => $row['user_id'],
                                  'role' => PL,
                                  'start_date' => $row['start_date'],
                                  'end_date' => $row['end_date'],
                                  'comment' => $row['comment']));
        $id = DBFunctions::insertId();
        DBFunctions::insert('grand_role_projects',
                            array('role_id' => $id,
                                  'project_id' => $row['project_id']));
    }

?>
