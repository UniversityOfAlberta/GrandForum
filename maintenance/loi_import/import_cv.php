<?php 

require_once('../commandLine.inc');

$year = date("Y");
$filename = "cv.csv";

$lois = array();
$query_tmp = 
    "INSERT INTO grand_researcher_cv(year, researcher_name, filename) 
     VALUES('%d', '%s', '%s')";

if (($handle = fopen($filename, "r")) !== FALSE) {
	$row = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($row>0){

            //$name = $data[0];
            $researcher_name = mysql_real_escape_string($data[0]);
            
            $filename = $data[1];

        	$query = sprintf($query_tmp, $year, $researcher_name, $filename);
        	$res = DBFunctions::execSQL($query, true);
        	if($res != 1){
                echo "Error: {$query}\n";
            }   
    	}
    	$row++;
    }
    fclose($handle);
}

//print_r($lois);