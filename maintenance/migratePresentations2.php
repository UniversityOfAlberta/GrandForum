<?php

require_once('commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}



$papers = Paper::getAllPapers('all', 'Presentation', 'both');

$i = 0;
foreach($papers as $paper){
    $type = $paper->getType();
    if($type == "2MM" || $type == "WIP" || $type == "RNotes"){
        $id = $paper->getId();
       
        $data = $paper->getData();
        if( !isset($data['event_title']) && !isset($data['event_location']) ){
            $conference = (isset($data['conference']))? $data['conference'] : "";
            $location = (isset($data['location']))? $data['location'] : "";
            unset($data['conference']);
            unset($data['location']);

            //$event_title = $data['event_title'];
            //$event_location = $data['event_location'];
            //$new_data = array();
            $data['event_title'] = $conference;
            $data['event_location'] = $location;
            //$new_data['organizing_body'] = "GRAND NCE";
            //$new_data['url'] = "";
            $new_data = serialize($data);

            $sql = "UPDATE grand_products
                    SET data = '{$new_data}'
                    WHERE id = {$id}";
            
            DBFunctions::execSQL($sql, true);
            echo "$id \n";

            $i++;
        }
    }
    else{
        continue;
    }

}

echo "Total Presentations Changed = $i \n\n";

/*
//Change Activities
$papers = Paper::getAllPapers('all', 'Activity', 'both');

$i = 0;
foreach($papers as $paper){
    $type = $paper->getType();
    if($type == "Presentation" || $type == "Invited Presentation"){
        $id = $paper->getId();
        $status = ($type == "Invited Presentation")? "Invited" : "Not Invited";
        
        $sql = "UPDATE grand_products
                SET category = 'Presentation',
                type = 'Misc',
                status = '{$status}'
                WHERE id = {$id}";
        
        DBFunctions::execSQL($sql, true);
        echo "$id \n";
        
        $i++;

    }
    else{
        continue;
    }

}

echo "Total Activities Changed = $i \n\n";
*/

function showHelp() {
		echo( <<<EOF
Marks whether or not the products in the given year have been reported to the RMC or NCE

USAGE: php productReported.php [--help] <year> <category> <type[NCE|RMC]> <strict[true|false]>

    <year>     : Specifies what reporting year this is for
    <category> : What type of product to report (ie. Publication, Artifact, all)
    <type>     : The type of report this was for (either NCE or RMC)
    <strict>   : Whether to count unpublished products (only for RMC)
    
	--help
		Show this help information

EOF
	);
}
?>

