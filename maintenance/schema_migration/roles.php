<?php
include_once('../commandLine.inc');

//change the schema
$sql = "CREATE TABLE grand_role (id INT NOT NULL PRIMARY KEY, name VARCHAR(255) NOT NULL)";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}


$values = array("INACTIVE", "HQP","CNI","PNI","PNIA","COPL","PL","COTL","TL","RMC","BOD","CHAMP","GOV","STAFF","MANAGER","_GOD_");

$i=0;
foreach($values as $v){
    $sql = "INSERT INTO grand_role (id, name) VALUES($i, '$v')";
    $result = execSQLStatement($sql,true);    
    echo "[".$result."] ". $sql."\n\n";
    if(!$result){ exit;}
    $i++;
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