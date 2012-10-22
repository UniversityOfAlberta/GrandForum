<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE mw_user_university ADD id INT NULL FIRST";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE mw_user_university DROP PRIMARY KEY";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE mw_user_university CHANGE id id INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE mw_user_university ADD start_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE mw_user_university ADD end_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE mw_user_university ADD COLUMN comment TEXT";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

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