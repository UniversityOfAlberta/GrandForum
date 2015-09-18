<?php
    /**This file is used to update the central repo with scopus information.
    This file takes in a csv file with names that the central repo will get information for. 
    It will attempt to do a curl command that calls the central repo server.
    The start year and end year is also required for some calls.
    Ruby De Jesus**/
    $USERNAME = "dev";
    $PASSWORD = "dev";
    $WORKSPACE = "fospubs";
    require_once( "commandLine.inc" );
    if(file_exists("facultyOfScience.csv")){
        print_r("Reading in data");
        $lines = explode("\n", file_get_contents("facultyOfScience.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $lname = @trim($cells[0]);
                $fname = @trim($cells[1]);
		$sciverse = @trim($cells[8]);
		if($sciverse == ""){continue;}
                $cmd = "curl -u \"cmput402:qpskcnvb\" -i";
                $cmd .= " \"http://199.116.235.47/centralrepo/v2/load/load_database\" ";
                $cmd .="-d \"firstname=$fname\" ";
                $cmd .="-d \"lastname=$lname\" ";
		$cmd .="-d \"sciverse=$sciverse\" ";
                $cmd .="-d \"username=$USERNAME\" ";
                $cmd .="-d \"workspace_name=$WORKSPACE\" ";
                $cmd .="-d \"password=$PASSWORD\" ";
                $cmd .=" -m 900";
		print_r($cmd . "\n");
                $result = "";
                exec($cmd,$result);
                print_r($result);
             }
         }
     }


?>
