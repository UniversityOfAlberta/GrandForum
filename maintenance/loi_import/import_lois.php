<?php 

require_once('../commandLine.inc');

$year = date("Y");
$filename = "lois2.csv";
$revision = 2;

$lois = array();
$query_tmp = 
    "INSERT INTO grand_loi(year, revision, name, full_name, type, related_loi, description, lead, colead, champion, primary_challenge, secondary_challenge, loi_pdf, supplemental_pdf) 
     VALUES('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";

if (($handle = fopen($filename, "r")) !== FALSE) {
	$row = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($row>0){

            $name = $data[0];
            $full_name = mysql_real_escape_string(nl2br($data[1]));
            $type = $data[2];

            $related_loi = "N/A";
            
            if($type == "Subproject"){
                if(!empty($data[3]) && $data[3]!="N/A"){
                    $related_loi = preg_replace('/;\s?/', ', ', $data[3]);
                }
            }
            else{
                if(!empty($data[4]) && $data[4]!="N/A"){
                    $related_loi = preg_replace('/;\s?/', ', ', $data[4]);
                }
            }

            $description = mysql_real_escape_string(str_replace("\n", '', $data[5]));
            //Lead/Co-Lead
            $lead = trim($data[6]);
            $lead = Person::newFromNameLike($lead);
            if($lead->getId()){
                $lead = mysql_real_escape_string($lead->getNameForForms());
            }else{
                $lead = mysql_real_escape_string(trim($data[6]));
            }

            $colead = trim($data[7]);
            $colead = Person::newFromNameLike($colead);
            if($colead->getId()){
                $colead = mysql_real_escape_string($colead->getNameForForms());
            }else{
                $colead = mysql_real_escape_string(trim($data[7]));
            }

            //Champ
            $champ_name = "";
            $champ_pos = "";
            $champ_org = "";
            list($champ_name, $champ_prof, $champ_org) = explode(';', $data[8]);
            if($champ_name){
                $champ = Person::newFromNameLike($champ_name);
                if($champ->getId()){
                    $champ_name = $champ->getNameForForms();
                    if($champ->getPosition()){
                        $champ_pos = $champ->getPosition();
                    }
                    if($champ->getUni()){
                        $champ_org = $champ->getUni();
                    }
                }
            }
            
            $champion = $champ_name;
            if($champ_pos){
                $champion .= "<br />".$champ_pos;
            }
            if($champ_org){
                $champion .= "<br />".$champ_org;
            }
            $champion = mysql_real_escape_string($champion);
            
            //Challenges
            $primary_challenge = mysql_real_escape_string($data[9]); 
            $secondary_challenge = mysql_real_escape_string($data[10]);
            $loi_pdf = $data[11];
            $supplemental_pdf = $data[12];

        	$query = sprintf($query_tmp, $year, $revision, $name, $full_name, $type, $related_loi, $description, $lead, $colead, $champion, $primary_challenge, $secondary_challenge, $loi_pdf, $supplemental_pdf);
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