<?php

require_once( "commandLine.inc" );

$wgUser = User::newFromId(1);

$lines = explode("\n", file_get_contents("grants/grants.csv"));
$copis = explode("\n", file_get_contents("grants/grants_copi.csv"));

// Grants
foreach($lines as $line){
    $csv = str_getcsv($line);
    if(count($csv) > 1){
        $id = trim($csv[0]); // Proposal Id	
        $short = trim($csv[1]); // Proposal Short Description
        $long = trim($csv[2]); // Proposal Long Description
        $status = trim($csv[4]); // Proposal Status
        $proposalBegin = trim($csv[5]); // Proposal Begin Date
        $proposalEnd = trim($csv[6]); // Proposal End Date
        $sponsor = trim($csv[8]); // Proposal Sponsor
        $createDate = trim($csv[14]); // Proposal Create Date
        $fiscalYear = trim($csv[15]); // Proposal Fiscal Year
        $empid = trim($csv[22]); // Principal Investigator Employee Id
        $pi = trim($csv[23]); // Principal Investigator Name
        $activatedDate = trim($csv[25]); //Award Activate Date
        $awardBegin = trim($csv[27]); // Award_Begin_Date
        $awardEnd = trim($csv[28]); // Award_End_Date
        $requestedValue = trim($csv[29]); // Total Requested Value
        $initialValue = trim($csv[30]); // Initial Total Award Value
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

// Grants Co-PI
foreach($copis as $line){
    $csv = str_getcsv($line);
    if(count($csv) > 1){
        $id = trim($csv[0]); // Proposal Id
        $empid = trim($csv[1]); // Principal Investigator Employee Id
        $role = trim($csv[3]); // Team Role
        if($role == "Co - PI"){
            $copi = Person::newFromEmployeeId($empid);
            $grant = Grant::newFromProjectId($id);
            if($grant->getId() != 0 && $copi->getId() != 0){
                $found = false;
                foreach($grant->getCoPI() as $person){
                    if($person instanceof Person && $person->getId() == $copi->getId()){
                        $found = true;
                        break;
                    }
                }
                if(!$found){
                    $grant->copi[] = $copi;
                    $grant->update();
                    echo "Adding Co-PI: {$id} - {$copi->getNameForForms()}\n";
                }
            }
        }
    }
}
        
?>
