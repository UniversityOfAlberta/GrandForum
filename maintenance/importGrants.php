<?php

require_once( "commandLine.inc" );

$wgUser = User::newFromId(1);

$lines = explode("\n", file_get_contents("grants/grants.csv"));

foreach($lines as $line){
    $csv = str_getcsv($line);
    if(count($csv) > 1){
        $id = trim($csv[0]); // Proposal Id	
        $short = trim($csv[1]); // Proposal Short Description
        $long = trim($csv[2]); // Proposal Long Description
        $empid = trim($csv[3]); // Principal Investigator Employee Id
        $pi = trim($csv[4]); // Principal Investigator Name
        $fiscalYear = trim($csv[8]); // Proposal Fiscal Year
        $createDate = trim($csv[9]); // Proposal Create Date
        $sponsor = trim($csv[10]); // Proposal Sponsor
        $status = trim($csv[14]); // Proposal Status
        $requestedValue = trim($csv[16]); // Total Requested Value
        $initialValue = trim($csv[17]); // Initial Total Award Value
        $activatedDate = trim($csv[18]); //Award Activate Date
        $proposalBegin = trim($csv[22]); // Proposal Begin Date
        $proposalEnd = trim($csv[23]); // Proposal End Date
        $awardBegin = trim($csv[24]); // Award_Begin_Date
        $awardEnd = trim($csv[25]); // Award_End_Date
        
        if($status == "Awarded"){
            $person = Person::newFromEmployeeId($empid);
            $grantByTitle = Grant::newFromTitle($short);
            $grantById = Grant::newFromProjectId($id);
            if($person->getId() != 0 && 
               $grantById->getId() == 0 && // Grant with the same id 
               ($grantByTitle->getId() == 0 || $grantByTitle->getProjectId() != "") // Grant with same title, but different id
            ){
                $_POST['user_id'] = $person->getId();
                $_POST['copi'] = array();
                $_POST['project_id'] = $id;
                $_POST['sponsor'] = $sponsor;
                $_POST['total'] = $initialValue;
                $_POST['title'] = $short;
                $_POST['scientific_title'] = $short;
                $_POST['description'] = $long;
                $_POST['start_date'] = $awardBegin;
                $_POST['end_date'] = $awardEnd;
                
                $api = new GrantAPI();
                $api->doPOST();
                
                echo "{$id}: {$awardBegin} - {$awardEnd}\n";
            }
        }
    }
}
        
?>
