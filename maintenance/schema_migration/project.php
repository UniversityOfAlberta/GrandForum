<?php/*
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE grand_project_descriptions ADD COLUMN full_name VARCHAR(255) AFTER name";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE grand_project_descriptions ADD COLUMN themes TEXT AFTER full_name";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE grand_project_descriptions MODIFY COLUMN description TEXT AFTER end_date";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

//Migrate the data
$sql = "UPDATE grand_project_descriptions d, grand_project p SET d.full_name = p.fullName WHERE d.project_id=p.id";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

//this one is still under development. Right now I'm planning to do this MANUALLY
//$sql = "UPDATE grand_project_descriptions d, grand_project_themes t SET d.themes = t.themes WHERE d.project_id=t.project_id";
//$result = execSQLStatement($sql);    
//echo "[".$result."] ". $sql."\n\n";
//if(!$result){ exit;}
     */

//Drop fullName from grand_project
/*$sql = "UPDATE grand_project DROP fullName";
$result = execSQLStatement($sql);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}*/

//Drop name from grand_project_descriptions
/*$sql = "UPDATE grand_project_descriptions DROP name";
$result = execSQLStatement($sql);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}*/

/*
//USER-PROJECT ASSOCIATION TABLE
$sql = "RENAME TABLE grand_projects TO grand_user_projects";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "UPDATE grand_user_projects up, grand_project p SET up.project_id = p.id WHERE up.project = p.name";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}

$sql = "ALTER TABLE grand_user_projects DROP COLUMN project";
$result = execSQLStatement($sql,true);    
echo "[".$result."] ". $sql."\n\n";
if(!$result){ exit;}
*/


/*
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
} */   
?>