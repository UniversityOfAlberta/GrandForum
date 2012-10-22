<?php

require_once('commandLine.inc');

$projects = Project::getAllProjects();
$nDeleted = 0;
$sql = "SELECT COUNT(*) as count
        FROM `grand_milestones`";
$data = DBFunctions::execSQL($sql);
$nStart = $data[0]['count'];

foreach($projects as $project){
    $milestones = $project->getMilestones(true);
    echo $project->getName()."\n";
    foreach($milestones as $milestone){
        while($milestone != null){
            $parent = $milestone->getParent();
            if($parent != null){
                if($parent->getAssessment() == $milestone->getAssessment() &&
                   $parent->getDescription() == $milestone->getDescription() &&
                   $parent->getTitle() == $milestone->getTitle() &&
                   $parent->getComment() == $milestone->getComment() &&
                   $parent->getProjectedEndDate() == $milestone->getProjectedEndDate() &&
                   ($parent->getStatus() == $milestone->getStatus() || 
                    ($milestone->getStatus() == "Continuing")) &&
                   count($parent->getPeople()) == count($milestone->getPeople())){
                   $skip = false;
                    foreach($parent->getPeople() as $oldPerson){
                        $found = false;
                        foreach($milestone->getPeople() as $newPerson){
                            if($oldPerson->getId() == $newPerson->getId()){
                                $found = true;
                            }
                        }
                        if(!$found){
                            $skip = true;
                            break;
                        }
                    }
                    if(!$skip){
                        if($milestone->getStatus() == "Continuing"){
                            $sql = "UPDATE `grand_milestones`
                                    SET `status` = '{$parent->getStatus()}',
                                        `start_date` = '{$parent->getStartDate()}'
                                    WHERE `id` = '{$milestone->getId()}'";
                            DBFunctions::execSQL($sql, true);
                            $id = $parent->getId();
                        }
                        else{
                            $sql = "UPDATE `grand_milestones`
                                    SET `start_date` = '{$parent->getStartDate()}'
                                    WHERE `id` = '{$milestone->getId()}'";
                            DBFunctions::execSQL($sql, true);
                            $id = $parent->getId();
                        }
                        $sql = "DELETE 
                                FROM `grand_milestones`
                                WHERE `id` = '{$id}'";
                        DBFunctions::execSQL($sql, true);
                        $sql = "DELETE 
                                FROM `grand_milestones_people`
                                WHERE `milestone_id` = '{$id}'";
                        DBFunctions::execSQL($sql, true);
                            echo "\tDeleted Duplicate: {$parent->getMilestoneId()} - {$id}\n";
                        $nDeleted++;
                    }
                }
            }
            $milestone = $parent;
        }
    }
}

$sql = "SELECT COUNT(*) as count
        FROM `grand_milestones`";
$data = DBFunctions::execSQL($sql);
$nRemaining = $data[0]['count'];

echo "\n== Summary Statistics ==\n";
echo "   # Start:\t{$nStart}\n";
echo "   # Deleted:\t{$nDeleted}\n";
echo "   # Remaining:\t{$nRemaining}\n";
echo "   % Remaining:\t".number_format($nRemaining/$nStart, 2)."\n";
?>
