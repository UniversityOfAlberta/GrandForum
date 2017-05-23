<?php

class PersonProjectsAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $projects = $person->getPersonProjects();
        if($this->getParam('personProjectId') != ""){
            // Single Project
            foreach($projects as $project){
                if($project['id'] == $this->getParam('personProjectId')){
                    return json_encode($project);
                }
            }
        }
        else{
            // All Projects
            return json_encode($projects);
        }
    }
    
    function doPOST(){
        $person = Person::newFromId($this->getParam('id'));
        $project = Project::newFromName($this->POST('name'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        if($project == null || $project->getName() == ""){
            $this->throwError("This Project does not exist");
        }
        if(!in_array($this->POST('name'), $allowedProjects) || 
           !in_array($project->getName(), $allowedProjects)){
            $this->throwError("You are not allowed to add this person to that project");
        }
        MailingList::unsubscribeAll($person);
        $status = DBFunctions::insert('grand_project_members',
                                      array('user_id'    => $person->getId(),
                                            'project_id' => $project->getId(),
                                            'start_date' => $this->POST('startDate'),
                                            'end_date'   => $this->POST('endDate'),
                                            'comment'    => $this->POST('comment')));

        $data = DBFunctions::select(array('grand_project_members'),
                                    array('id'),
                                    array('project_id' => $project->getId(),
                                          'user_id' => $person->getId()),
                                    array('id' => 'DESC'),
                                    array(1));
        if(count($data) > 0){
            $this->params['personProjectId'] = $data[0]['id'];
        }
        $person->projects = null;
        MailingList::subscribeAll($person);
        return $this->doGET();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        $project = Project::newFromName($this->POST('name'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        if($project == null || $project->getName() == ""){
            $this->throwError("This Project does not exist");
        }
        if(!in_array($this->POST('name'), $allowedProjects) || 
           !in_array($project->getName(), $allowedProjects)){
            $this->throwError("You are not allowed to add this person to that project");
        }
        MailingList::unsubscribeAll($person);
        $status = DBFunctions::update('grand_project_members',
                                      array('project_id' => $project->getId(),
                                            'start_date' => $this->POST('startDate'),
                                            'end_date'   => $this->POST('endDate'),
                                            'comment'    => $this->POST('comment')),
                                      array('id' => $this->getParam('personProjectId')));
        $person->projects = null;
        MailingList::subscribeAll($person);
        if(!$status){
            $this->throwError("The project <i>{$project->getName()}</i> could not be updated");
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        $data = DBFunctions::select(array('grand_project_members'),
                                    array('project_id'),
                                    array('id' => EQ($this->getParam('personProjectId'))));
        if(count($data) > 0){
            $project = Project::newFromId($data[0]['project_id']);
            if(!in_array($project->getName(), $allowedProjects)){
                $this->throwError("You are not allowed to remove this person from that project");
            }
        }
        else{
            $this->throwError("This Project does not exist");
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::delete('grand_project_members',
                            array('id' => $this->getParam('personProjectId')));
        $person->projects = null;
        MailingList::subscribeAll($person);
        return false;
    }
}

?>
