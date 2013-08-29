<?php
include_once('../commandLine.inc');

//change the schema
$sql = "SELECT * FROM grand_acknowledgements";
$data = execSQLStatement($sql);

foreach($data as $row){
    $id = $row['id'];
    $user_id = $row['user_id'];
    $person_name = $row['user_name'];
    $date = $row['date'];
    $super_name = $row['supervisor'];

    if($user_id > -1){
        $person = Person::newFromId($user_id);
        $person_name = $person->getName();
    }
    
    $super = Person::newFromNameLike($super_name);
    if($super->getName() != ""){
        $super_name = $super->getName();
    }
    
    $php_date = date_create_from_format('d-m-y', $date);
    if($php_date){
        $date =  $php_date->format('Y-m-d');
        //echo $date . "GOOD\n";
    }else{
        $php_date = date_create_from_format('d-M-y', $date);
        if($php_date){
            $date = $php_date->format('Y-m-d');
            //echo $date . "GOOD-2\n";
        }else{
            echo $date . " DATE FAIL\n";
            $date = "0000-00-00";
        }
    }
    $person_name = mysql_real_escape_string($person_name);
    $super_name = mysql_real_escape_string($super_name);

    $update_sql = "UPDATE grand_acknowledgements 
                   SET user_name='{$person_name}', date='{$date}', supervisor='{$super_name}' 
                   WHERE id={$id}";
    
    //echo $update_sql . "\n";

    $res = execSQLStatement($update_sql, true);



}


echo "ALL DONE!\n";

//---------------HELPERS
function execSQLStatement($sql, $update=false){
    if($update == false){
        $dbr = wfGetDB(DB_SLAVE);
    }
    else {
        $dbr = wfGetDB(DB_MASTER);
        return $dbr->query($sql);
    }
    $result = $dbr->query($sql);
    $rows = null;
    if($update == false){
        $rows = array();
        while ($row = $dbr->fetchRow($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}    
?>