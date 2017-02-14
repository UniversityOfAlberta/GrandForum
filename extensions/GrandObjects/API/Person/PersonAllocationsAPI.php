<?php

class PersonAllocationsAPI extends RESTAPI {
    
    function doGET(){
        global $config;
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $array = array();
            $person = Person::newFromId($this->getParam('id'));
            if(!$me->isRoleAtLeast(MANAGER)){
                $this->throwError("You are not allowed to access this API");
            }
            $startYear = date('Y');
            $endYear = $config->getValue('projectPhaseDates');
            $endYear = $endYear[1];
            for($y = $startYear; $y >= $endYear; $y--){
                $projects = array();
                foreach($person->getAllocatedAmount($y, null, true) as $key => $amount){
                    $project = Project::newFromId($key);
                    $projects[] = array("name" => $project->getName(),
                                        "amount" => $amount);
                }
                $array[$y] = array('total' => $person->getAllocatedAmount($y),
                                   'projects' => $projects);
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
