<?php
    /**This file is used to update the central repo with grant information.
    This file takes in a csv file with names that the central repo will get information for. 
    It will attempt to do a curl command that calls the central repo server.
    The start year and end year is also required for some calls.
    Ruby De Jesus**/
    $USERNAME = "dev";
    $PASSWORD = "dev";
    $WORKSPACE = "dev";
    $START_YEAR = "2011";
    $END_YEAR = "2015";
    require_once( "commandLine.inc" );
    if(file_exists("missedFOS.csv")){
        print_r("Reading in data");
        $lines = explode("\n", file_get_contents("missedFOS.csv"));
        foreach($lines as $line){
            $cells = str_getcsv($line);
            if(count($cells) > 1){
                $lname = @trim($cells[0]);
                $fname = @trim($cells[1]);
		   //loading NSERC grants using curl command
                $university = @trim($cells[4]);
                $cmd = "curl -u \"cmput402:qpskcnvb\" -i";
		$cmd .= " \"http://199.116.235.47/centralrepo/v2/loadNSERCgrants\" "; 
	        $cmd .="-d \"firstname=$fname\" ";  
		$cmd .="-d \"lastname=$lname\" ";
		$cmd .="-d \"institution=$university\" ";
		$cmd .="-d \"username=$USERNAME\" ";
		$cmd .="-d \"workspace_name=$WORKSPACE\" ";
		$cmd .="-d \"password=$PASSWORD\" ";
		$cmd .="-d \"start_year=$START_YEAR\" ";
	   	$cmd .="-d \"end_year=$END_YEAR\"";
                print_r($cmd);
		$result = "";
		exec($cmd,$result);
                print_r($result);
             }
         }
	  //loading CIHR grants (only required to run once)
       $cmd = "curl -u \"cmput402:qpskcnvb\" -i"; 
       $cmd .=" \"http://199.116.235.47/centralrepo/v2/loadCIHRgrants\" "; 
       $cmd .="-d \"start_year=$START_YEAR\" ";
       $cmd .="-d \"username=$USERNAME\" ";
       $cmd .="-d \"workspace_name=$WORKSPACE\" ";
       $cmd .="-d \"password=$PASSWORD\"";
       print_r($cmd);
       exec($cmd,$result);
       print_r($result);
	
     }


?>
