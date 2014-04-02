<?php
require_once( 'commandLine.inc' );

$wgUser = User::newFromName("Admin");

$projects = Project::getAllProjectsEver();
foreach($projects as $project){
    if($project->getPhase() == 1){
        echo $project->getName()."\n";
        if(!$project->getStatus() == 'Ended'){
            $_POST['project'] = $project->getName();
            $_POST['effective_date'] = '2014-03-31 23:59:59';
            APIRequest::doAction('DeleteProject', true);
        }
        foreach($project->getAllPeople() as $person){
            DBFunctions::execSQL("UPDATE `grand_project_members`
                                  SET end_date = '2014-03-31 23:59:59'
                                  WHERE user_id = '{$person->getId()}'
                                  AND project_id = '{$project->getId()}'
                                  AND end_date = '0000-00-00 00:00:00'", true);
        }
        foreach($project->getLeaders() as $leader){
            DBFunctions::execSQL("UPDATE `grand_project_leaders`
                                  SET end_date = '2014-03-31 23:59:59'
                                  WHERE user_id = '{$leader->getId()}'
                                  AND project_id = '{$project->getId()}'
                                  AND end_date = '0000-00-00 00:00:00'", true);
            //echo $leader->getName()."\n";
        }
        foreach($project->getCoLeaders() as $leader){
            DBFunctions::execSQL("UPDATE `grand_project_leaders`
                                  SET end_date = '2014-03-31 23:59:59'
                                  WHERE user_id = '{$leader->getId()}'
                                  AND project_id = '{$project->getId()}'
                                  AND end_date = '0000-00-00 00:00:00'", true);
            echo "\t".$leader->getName()."\n";
        }
        foreach($project->getAllPreds() as $pred){
            foreach($pred->getAllPeople() as $person){
                DBFunctions::execSQL("UPDATE `grand_project_members`
                                      SET end_date = '2014-03-31 23:59:59'
                                      WHERE user_id = '{$person->getId()}'
                                      AND project_id = '{$pred->getId()}'
                                      AND end_date = '0000-00-00 00:00:00'", true);
            }
            foreach($pred->getLeaders() as $leader){
                DBFunctions::execSQL("UPDATE `grand_project_leaders`
                                      SET end_date = '2014-03-31 23:59:59'
                                      WHERE user_id = '{$leader->getId()}'
                                      AND project_id = '{$pred->getId()}'
                                      AND end_date = '0000-00-00 00:00:00'", true);
                //echo $leader->getName()."\n";
            }
            foreach($pred->getCoLeaders() as $leader){
                DBFunctions::execSQL("UPDATE `grand_project_leaders`
                                      SET end_date = '2014-03-31 23:59:59'
                                      WHERE user_id = '{$leader->getId()}'
                                      AND project_id = '{$pred->getId()}'
                                      AND end_date = '0000-00-00 00:00:00'", true);
                echo "\t".$leader->getName()."\n";
            }
        }
    }
}
?>
