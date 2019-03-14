<?php

    require_once('commandLine.inc');
    
    $wgUser = User::newFromId(1);
    
    $projects = Project::getAllProjectsEver();

    $structure = Product::structure();
    
    foreach($structure['categories'] as $category => $ts){
        $alreadyCounted = array();
        $typeCounts = array();
        $types = array_keys($ts['types']);
        echo "$category\n";
        echo "WP#,Project leads,University research centre,Type,Number\n";
        foreach($types as $type){
            $typeCounts[$type] = 0;
        }
        foreach($projects as $project){
            $products = $project->getPapers($category, '0000-00-00', '2020-03-31');
            foreach($types as $type){
                $count = 0;
                foreach($products as $product){
                    if($product->getType() == $type){
                        $count++;
                        if(!isset($alreadyCounted[$product->getId()])){
                            $typeCounts[$type]++;
                        }
                        $alreadyCounted[$product->getId()] = true;
                    }
                }
                
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
            echo @"\"Total Network\",,,\"$type\",{$typeCounts[$type]}\n";
        }
        echo "\n";
    }

?>
