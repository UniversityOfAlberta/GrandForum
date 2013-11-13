<?php

class ProjectChampionsAPI extends API{

    function ProjectChampionsAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("champion_id",true,"The id of the champion","1");
    }

    function processParams($params){

    }

    function doAction($noEcho=false){
        $project = Project::newFromName($_POST['project']);
        if(!$noEcho){
            if($project == null || $project->getName() == null){
                echo "A valid project must be provided\n";
                exit;
            }
            $person = Person::newFromName($_POST['user_name']);
            $isLead = false;
            foreach($me->getLeadProjects() as $p){
                if($p->getId() == $project->getId()){
                    $isLead = true;
                    break;
                }
            }
            if(!$isLead){
                echo "You must be logged in as a project leader\n";
                exit;
            }
        }
        
        if(isset($_POST['champion_id']) && !empty($_POST['champion_id']) && $_POST['champion_id'] != 0){
            DBFunctions::begin();
            $data = DBFunctions::select(array('grand_project_champions'),
                                        array('id'),
                                        array('project_id' => EQ($project->getId()),
                                              'user_id' => EQ($_POST['champion_id']),
                                              'end_date' => EQ('0000-00-00 00:00:00')),
                                        array('id' => 'DESC'),
                                        array(1));
            if(count($data) == 0){
                // Insert
                DBFunctions::insert('grand_project_champions',
                                    array('project_id' => $project->getId(),
                                          'user_id' => $_POST['champion_id'],
                                          'start_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                                    true);
            }
            DBFunctions::commit();
            if(!$noEcho){
                echo "Project champion updated\n";
            }
        }
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
