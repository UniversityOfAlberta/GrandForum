<?php

require_once('commandLine.inc');

$committees = $config->getValue('committees');
$roleAliases = $config->getValue('roleAliases');

$roles = DBFunctions::execSQL("SELECT * 
                               FROM `grand_roles` r, `mw_user` u
                               WHERE r.id NOT IN (SELECT role_id FROM grand_role_projects)
                               AND r.user_id = u.user_id
                               AND u.deleted = 0");
$inserts = array();
$deletes = array();
foreach($roles as $role){
    if($role['role'] == ADMIN ||
       $role['role'] == MANAGER ||
       $role['role'] == STAFF ||
       isset($committees[$role['role']]) ||
       isset($roleAliases[$role['role']])){
        continue;
    }
    $projects = DBFunctions::execSQL("SELECT p.*, pm.start_date, pm.end_date, pm.comment
                                      FROM `grand_project` p, `grand_project_members` pm
                                      WHERE pm.`user_id` = '{$role['user_id']}'
                                      AND p.id = pm.project_id");
    foreach($projects as $project){
        $check = DBFunctions::execSQL("SELECT *
                                       FROM `grand_roles` r, `grand_role_projects` rp
                                       WHERE r.id = rp.role_id
                                       AND r.user_id = '{$role['user_id']}'
                                       AND rp.project_id = '{$project['id']}'");
        if(count($check) == 0){
            $rStart = $role['start_date'];
            $rEnd = $role['end_date'];
            $pStart = $project['start_date'];
            $pEnd = $project['end_date'];
            $start = max($rStart, $pStart);
            $end = max($rEnd, $pEnd);
            $comment = $role['comment'];
            if($comment != "" && $project['comment'] != ""){
                $comment .= "\n";
            }
            $comment .= $project['comment'];
            
            $inserts[] = array('user_id' => $role['user_id'],
                               'role' => $role['role'],
                               'start_date' => $start,
                               'end_date' => $end,
                               'project' => $project['id'],
                               'comment' => $comment);
            $deletes[$role['id']] = $role['id'];
            echo "{$role['user_name']}: {$role['role']}\t-> {$project['name']} ({$start}, {$end})\n";
        }
    }
}

foreach($inserts as $insert){
    DBFunctions::insert('grand_roles',
                        array('user_id' => $insert['user_id'],
                              'role' => $insert['role'],
                              'start_date' => $insert['start_date'],
                              'end_date' => $insert['end_date'],
                              'comment' => $insert['comment']));
    $id = DBFunctions::insertId();
    DBFunctions::insert('grand_role_projects',
                        array('role_id' => $id,
                              'project_id' => $insert['project']));
}

?>
