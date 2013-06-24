<?php 

require_once('../commandLine.inc');

$year = date("Y");
$filename = "lois.csv";

$lois = array();
$query_tmp = 
    "INSERT INTO grand_loi(year, name, full_name, type, related_loi, description, lead, colead, champion, primary_challenge, secondary_challenge, loi_pdf, supplemental_pdf) 
     VALUES('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";

if (($handle = fopen($filename, "r")) !== FALSE) {
	$row = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($row>0){

            $name = $data[0];
            $full_name = mysql_real_escape_string(nl2br($data[1]));
            $type = $data[2];

            $related_loi = "N/A";
            if(!empty($data[3]) && $data[3]!="N/A"){
                $related_loi = $data[3];
            }
            else if(!empty($data[4]) && $data[4]!="N/A"){
                $related_loi = $data[3];
            }

            $description = mysql_real_escape_string(nl2br($data[5]));
            $lead = mysql_real_escape_string(nl2br($data[6]));
            $colead = mysql_real_escape_string(nl2br($data[7]));
            $champion = mysql_real_escape_string(nl2br($data[8]));
            $primary_challenge = mysql_real_escape_string(nl2br($data[9])); 
            $secondary_challenge = mysql_real_escape_string(nl2br($data[10]));
            $loi_pdf = $data[11];
            $supplemental_pdf = $data[12];

        	$query = sprintf($query_tmp, $year, $name, $full_name, $type, $related_loi, $description, $lead, $colead, $champion, $primary_challenge, $secondary_challenge, $loi_pdf, $supplemental_pdf);
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