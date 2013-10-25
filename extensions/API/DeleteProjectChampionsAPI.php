<?php

class DeleteProjectChampionsAPI extends API{

    function DeleteProjectChampionsAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("champion_id",true,"The id of the champion","1");
        $this->addPOST("effective_date",true,"The date that the champion leaves the project","2010-10-10");
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
        
        if(isset($_POST['champion_id']) && !empty($_POST['champion_id'])){
            DBFunctions::begin();
            $data = DBFunctions::select(array('grand_project_champions'),
                                        array('id'),
                                        array('project_id' => EQ($project->getId()),
                                              'user_id' => EQ($_POST['champion_id'])),
                                        array('id' => 'DESC'),
                                        array(1));
            $last_champ_id = (isset($data[0]['id']))? $data[0]['id'] : null;
            if(isset($data[0]['id'])){
                $endDate = EQ(COL('CURRENT_TIMESTAMP'));
                if(isset($_POST['effective_date'])){
                    $endDate = $_POST['effective_date'];
                }
                DBFunctions::update('grand_project_champions',
                                    array('end_date' => $endDate),
                                    array('id' => $last_champ_id,
                                          'project_id' => $project->getId(),
                                          'user_id' => $_POST['champion_id']),
                                    true);
            }
            DBFunctions::commit();
            if(!$noEcho){
                echo "Project champion deleted\n";
            }
        }
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
