<?php

class GradChairAPI extends RESTAPI {
    
    function doGET(){
        $hqpId = $this->getParam('hqpId');
        $hqp = Person::newFromId($hqpId);
        if($hqpId != "" && $hqp->getId() != 0){
            $gradchair = GradChair::newFrom($hqpId);
        }
        else{
            $me = Person::newFromWgUser();
            $gradchair = new Collection(GradChair::getAllByDepartment($me->getDepartment()));
        }
        return $gradchair->toJSON();
    }
    
    function doPOST(){
        return $this->doPUT();
    }
    
    function doPUT(){
        $hqpId = $this->getParam('hqpId');
        $hqp = Person::newFromId($hqpId);
        if($hqpId != "" && $hqp->getId() != 0){
            $gradchair = GradChair::newFromId($hqpId);
            $gradchair->background = $this->POST("background");
            $gradchair->background_notes = $this->POST("background_notes");
            $gradchair->meetings = $this->POST("meetings");
            $gradchair->meetings_notes = $this->POST("meetings_notes");
            $gradchair->ethics = $this->POST("ethics");
            $gradchair->ethics_notes = $this->POST("ethics_notes");
            $gradchair->courses = $this->POST("courses");
            $gradchair->courses_notes = $this->POST("courses_notes");
            $gradchair->notes = $this->POST("notes");
            $gradchair->update();
            return $gradchair->toJSON();
        }
        else{
            $this->throwError("This HQP doesn't exist");
        }
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

?>
