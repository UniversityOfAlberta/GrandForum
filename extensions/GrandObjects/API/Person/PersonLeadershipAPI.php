<?php

class PersonLeadershipAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $projects = $person->getPersonLeaderships();
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
        global $config, $wgScriptPath;
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
        if(!$me->isRoleAtLeast(STAFF)){
            $this->throwError("You are not allowed to add this person to that project");
        }
        MailingList::unsubscribeAll($person);
        if(!$person->isMemberOf($project)){
            $api = new PersonProjectsAPI();
            $api->params = $this->params;
            $api->doPOST();
        }
        $status = DBFunctions::insert('grand_project_leaders',
                                      array('user_id'    => $person->getId(),
                                            'project_id' => $project->getId(),
                                            'start_date' => $this->POST('startDate'),
                                            'end_date'   => $this->POST('endDate'),
                                            'comment'    => $this->POST('comment')));

        $id = DBFunctions::insertId();
        $this->params['personProjectId'] = $id;
        
        Notification::addNotification($me, Person::newFromId(0), "Project Leader Added", "Effective {$this->POST('startDate')} <b>{$person->getNameForForms()}</b> is a project leader of <b>{$project->getName()}</b>", "{$person->getUrl()}");
        if($config->getValue("networkName") == "CFN" && $person->isRoleDuring(HQP, "1900-01-01", "2100-01-01") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Project Leader Added", "Effective {$this->POST('startDate')} <b>{$person->getNameForForms()}</b> becomes a project leader of <b>{$project->getName()}</b>", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")));
        }
        Notification::addNotification($me, $person, "Project Leader Added", "Effective {$this->POST('startDate')} you become a project leader of <b>{$project->getName()}</b>", "{$person->getUrl()}");
        MailingList::subscribeAll($person);
        return $this->doGET();
    }
    
    function doPUT(){
        global $config, $wgScriptPath;
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
        if(!$me->isRoleAtLeast(STAFF)){
            $this->throwError("You are not allowed to add this person to that project");
        }
        MailingList::unsubscribeAll($person);
        $status = DBFunctions::update('grand_project_leaders',
                                      array('project_id' => $project->getId(),
                                            'start_date' => $this->POST('startDate'),
                                            'end_date'   => $this->POST('endDate'),
                                            'comment'    => $this->POST('comment')),
                                      array('id' => $this->getParam('personProjectId')));
        Notification::addNotification($me, Person::newFromId(0), "Project Leader Changed", "The project leadership ({$project->getName()}) of <b>{$person->getNameForForms()}</b> has been changed", "{$person->getUrl()}");
        if($config->getValue("networkName") == "CFN" && $person->isRoleDuring(HQP, "1900-01-01", "2100-01-01") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Project Leader Changed", "The project leadership ({$project->getName()}) of <b>{$person->getNameForForms()}</b> has been changed", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")));
        }
        if($this->POST('endDate') != '0000-00-00 00:00:00'){
            Notification::addNotification($me, $person, "Project Leader Removed", "Effective {$this->POST('endDate')} you are no longer a leader of <b>{$project->getName()}</b>", "{$person->getUrl()}");
        }
        MailingList::subscribeAll($person);
        if(!$status){
            $this->throwError("The project <i>{$project->getName()}</i> could not be updated");
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        global $config, $wgScriptPath;
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedProjects = $me->getAllowedProjects();
        $data = DBFunctions::select(array('grand_project_leaders'),
                                    array('project_id'),
                                    array('id' => EQ($this->getParam('personProjectId'))));
        if(count($data) > 0){
            $project = Project::newFromId($data[0]['project_id']);
            if(!in_array($project->getName(), $allowedProjects) || !$me->isRoleAtLeast(STAFF)){
                $this->throwError("You are not allowed to remove this person from that project");
            }
        }
        else{
            $this->throwError("This Project does not exist");
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::delete('grand_project_leaders',
                            array('id' => $this->getParam('personProjectId')));
        Notification::addNotification($me, Person::newFromId(0), "Project Leader Removed", "<b>{$person->getNameForForms()}</b> is no longer leader of <b>{$project->getName()}</b>", "{$person->getUrl()}");
        if($config->getValue("networkName") == "CFN" && $person->isRoleDuring(HQP, "1900-01-01", "2100-01-01") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Project Leader Removed", "<b>{$person->getNameForForms()}</b> is no longer leader of <b>{$project->getName()}</b>", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")));
        }
        Notification::addNotification($me, $person, "Project Leader Removed", "You are no longer a leader of <b>{$project->getName()}</b>", "{$person->getUrl()}");
        MailingList::subscribeAll($person);
        return json_encode(array());
    }
}

?>
