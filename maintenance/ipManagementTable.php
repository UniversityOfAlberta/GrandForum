<?php

    require_once('commandLine.inc');
    
    $wgUser = User::newFromId(1);
    
    $projects = Project::getAllProjects();
    
    $types = array('Copyright',
                   'IP Disclosure',
                   'License Agreement',
                   'Patent',
                   'Patent Cooperation Treaty (PCT)',
                   'Provisional Patent',
                   'Report of Invention',
                   'Trademark');
    
    echo "WP#,Project leads,University research centre,IP Category,Number of Ips protections\n";
    
    $typeCounts = array();
    
    foreach($projects as $project){
        $products = $project->getPapers('KTEE - Commercialization', '2017-04-01', '2018-03-31');
        foreach($types as $type){
            $count = 0;
            foreach($products as $product){
                if($product->getType() == $type){
                    $count++;
                }
            }
            @$typeCounts[$type] += $count;
            $leaders = $project->getLeaders();
            $leaderNames = array();
            $leaderUnis = array();
            foreach($leaders as $leader){
                $leaderNames[] = $leader->getNameForForms();
                $leaderUnis[] = $leader->getUni();
            }
            $leaderNames = implode(", ", $leaderNames);
            $leaderUnis = implode(", ", $leaderUnis);
            echo "\"{$project->getName()}\",\"{$leaderNames}\",\"{$leaderUnis}\",\"$type\",{$count}\n";
        }
    }
    
    foreach($types as $type){
        echo "\"Total Network\",,,\"$type\",{$typeCounts[$type]}\n";
    }

?>
