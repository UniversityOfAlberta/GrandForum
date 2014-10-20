<?php

    require_once('commandLine.inc');
    
    $lines = explode("\n", file_get_contents("alloc.csv"));
    $projects = array();
    foreach($lines as $i => $line){
        $cells = str_getcsv($line);
        if($i == 0){
            foreach($cells as $j => $proj){
                if($j > 0){
                    $projects[$j] = Project::newFromName($proj);
                }
            }
        }
        else{
            $uId = $cells[0];
            foreach($cells as $j => $alloc){
                if($j > 0 && $alloc > 0){
                    $project = $projects[$j];
                    if($project != null){
                        DBFunctions::insert('grand_allocations',
                                            array('user_id' => $uId,
                                                  'project_id' => $project->getId(),
                                                  'year' => REPORTING_YEAR,
                                                  'amount' => $alloc));
                    }
                }
            }
        }
    }

?>
