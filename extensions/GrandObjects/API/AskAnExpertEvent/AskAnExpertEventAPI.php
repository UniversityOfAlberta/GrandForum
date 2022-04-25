<?php

/**
 * Class Avoid Resource
 * API Class for interacting with individual Avoid Resource
 */
class AskAnExpertEventAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $ask_an_expert = AskAnExpertEvent::newFromId($this->getParam('id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this");
            }
            return $ask_an_expert->toJSON();
	}
	else{
		//$ask_an_expert_all = new Colleddction(AskAnExpertEvent::getAllExpertEvents());
		//
		//return $ask_an_expert_all->toJSON();
		$ask_an_expert = AskAnExpertEvent::newFromId(1);
		return $ask_an_expert->toJSON();

        }

    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
        $askanexpertevent = new AskAnExpertEvent(array());
        header('Content-Type: application/json');
        $askanexpertevent->name_of_expert = $this->POST('name_of_expert');
        $askanexpertevent->expert_field = $this->POST('expert_field');
	//get date and time
	$date = $this->POST('date_of_event');
	$time = $this->POST('time_of_event');
	$askanexpertevent->date_of_event = "$date $time";
        $askanexpertevent->active = true;
        $status =$askanexpertevent->create();
        if(!$status){
            $this->throwError("The Event could not be created");
        }
        $askanexpertevent = AvoidResource::newFromId($askanexpertevent->getId());
        return$askanexpertevent->toJSON();
    }


  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        $askanexpertevent = AskAnExpertEvent::newFromId($this->getParam('id'));
        if($askanexpertevent == null || $askanexpertevent->name_of_expert == ""){
            $this->throwError("This event does not exist");
        }
        header('Content-Type: application/json');
        $askanexpertevent->name_of_expert = $this->POST('name_of_expert');
	$askanexpertevent->expert_field = $this->POST('expert_field');
	$askanexpertevent->date_of_event = $this->POST('date_of_event');
	$askanexpertevent->zoomlink = $this->POST('zoomlink');
        $askanexpertevent->active = 1;
        $status = $askanexpertevent->update();
        if(!$status){
            $this->throwError("The Event could not be updated");
        }
        $askanexpertevent = AskAnExpertEvent::newFromId($this->getParam('id'));
        return $askanexpertevent->toJSON();
    }

  /**
   * doDELETE handler for delete request method
   * @return bool
   */
    function doDELETE(){
        return false;
    }
}

?>
