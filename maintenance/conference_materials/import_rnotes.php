<?php
require_once( "../commandLine.inc" );
global $wgServer, $wgScriptPath;

$current_year = "2013";
$target = "rnotes";
$type = "RNotes";
$date = "2013-05-14";
$status = "Not Invited";

$string = file_get_contents("csv/{$current_year}/{$target}.csv");
$count = 0;
foreach(explode("\n", $string) as $line){
    if($count++ == 0){ continue; }

    $line = str_replace("”", "'", str_replace("“", "'", str_replace("ʼ", "'", $line)));
    $split = str_getcsv($line, ",", "\"");
    if(!isset($split[1])){
        continue;
    }

    $file = $split[0];
    $title = mysql_escape_string($split[1]);
    $authors_arr = explode(',', $split[2]);
    $authors = array();
    foreach ($authors_arr as $a){
        $authors[] = trim($a);
    }

    $projects_arr = explode(',', $split[3]);
    $projects = array();
    foreach($projects_arr as $p){
        $projects[] = strtoupper(trim($p));
    }
    //$projects_str = implode(', ', pieces)
    
    if($file){
        $file = "https://forum.grand-nce.ca/index.php/File:".$file;
    }
    else{
        $file = "";
    }

    $data = array(
            "organizing_body" => "GRAND NCE",
            "url"=>$file,
            "event_title"=>"GRAND Annual Conference 2013",
            "event_location"=> "Toronto, Canada",
            );

    $sql = "INSERT INTO grand_products (`description`,`category`,`projects`,`type`,`title`,`date`,`venue`,`status`,`authors`,`data`)
           VALUES ('','Presentation','".serialize($projects)."','{$type}','{$title}','{$date}','','{$status}','".serialize($authors)."','".serialize($data)."')";

    $result = DBFunctions::execSQL($sql, true);
    //echo mysql_insert_id(DBFunctions::$dbw);
   /// var_dump(DBFunctions::$dbw->mConn);
    $last_id = DBFunctions::$dbw->insertId();

    
    if($result != 1){
        echo "ERROR: {$sql}\n";
    }

    echo "**[[Presentation:{$last_id} | {$title}]]\n";

}
?>
