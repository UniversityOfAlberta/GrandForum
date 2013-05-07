<?php

require_once('../commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}

migrateMisc();

function migrateMisc(){

    $papers = Paper::getAllPapers('all', 'all', 'both');

    $i = 0;
    foreach($papers as $paper){
        $id = $paper->getId();
        $type = $paper->getType();

        if( preg_match("/Misc:/", $type) ){    
            $types = preg_split("/: /", $type);
            $misc_type = $types[1];
            //echo "$misc_type \n";
            $data_changed = false;
            $data = $paper->getData();
            
            $data["misc"] = $misc_type;

            $new_data = serialize($data);

            $sql = "UPDATE grand_products
                    SET type='Misc', data = '{$new_data}'
                    WHERE id = {$id}";
            
            DBFunctions::execSQL($sql, true);
            echo "$id \n";

            $i++;
        }
        else{
            continue;
        }

    }

    echo "Total Misc Types = $i \n\n";
}