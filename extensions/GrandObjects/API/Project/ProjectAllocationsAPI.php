<?php

class ProjectAllocationsAPI extends RESTAPI {
    
    function doGET(){
        global $config;
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $array = array();
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
            }
            if(!$me->isRoleAtLeast(MANAGER)){
                $this->throwError("You are not allowed to access this API");
            }
            $startYear = date('Y');
            $endYear = $config->getValue('projectPhaseDates');
            $endYear = $endYear[1];
            for($y = $startYear; $y >= $endYear; $y--){
                $array[$y] = $project->getAllocatedAmount($y);
            }
            return json_encode($array);
        }
    }
    
    function doPOST(){
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doGET();
    }
    
    function doDELETE(){
        return $this->doGET();
    }
}

?>
