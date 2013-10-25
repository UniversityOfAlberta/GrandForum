<?php

class ProjectChampionsAPI extends API{

    function ProjectChampionsAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
        $this->addPOST("champion_id",true,"The id of the champion","1");
        $this->addPOST("champion_org",true,"Organization of the champion","JDoe Inc.");
        $this->addPOST("champion_title",true,"Title of the champion","Chief Technology Officer");
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
            DBFunctions::insert('grand_project_champions',
                                array('project_id' => $project->getId(),
                                      'user_id' => $_POST['champion_id'],
                                      'champion_org' => $_POST['champion_org'],
                                      'champion_title' => $_POST['champion_title'],
                                      'start_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                                true);
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
