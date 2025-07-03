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
            $ask_an_expert_all = AskAnExpertEvent::getAllExpertEvents();
            $collection = new Collection($ask_an_expert_all);
            return $collection->toJSON();
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
        $askanexpertevent->date_of_event = $this->POST('date_of_event');
        $askanexpertevent->end_of_event = $this->POST('end_of_event');
        $askanexpertevent->zoomlink = $this->POST('zoomlink');
        $askanexpertevent->date_for_questions = $this->POST('date_for_questions');
        $askanexpertevent->event = $this->POST('event');
        $askanexpertevent->desc = $this->POST('description');
        $askanexpertevent->host = $this->POST('host');
        $askanexpertevent->theme = $this->POST('theme');
        $askanexpertevent->details = $this->POST('details');
        $askanexpertevent->location = $this->POST('location');
        $askanexpertevent->active = $this->POST('active');
        $askanexpertevent->currently_on = 1;
        $status =$askanexpertevent->create();
        if(!$status){
            $this->throwError("The Event could not be created");
        }
        $askanexpertevent = AskAnExpertEvent::newFromId($askanexpertevent->getId());
        return$askanexpertevent->toJSON();
    }


  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        $askanexpertevent = AskAnExpertEvent::newFromId($this->getParam('id'));
        if($askanexpertevent == null){
            $this->throwError("This event does not exist");
        }
        header('Content-Type: application/json');
        $askanexpertevent->name_of_expert = $this->POST('name_of_expert');
        $askanexpertevent->expert_field = $this->POST('expert_field');
        $askanexpertevent->date_of_event = $this->POST('date_of_event');
        $askanexpertevent->end_of_event = $this->POST('end_of_event');
        $askanexpertevent->zoomlink = $this->POST('zoomlink');
        $askanexpertevent->date_for_questions = $this->POST('date_for_questions');
        $askanexpertevent->event = $this->POST('event');
        $askanexpertevent->desc = $this->POST('description');
        $askanexpertevent->host = $this->POST('host');
        $askanexpertevent->theme = $this->POST('theme');
        $askanexpertevent->details = $this->POST('details');
        $askanexpertevent->location = $this->POST('location');
        $askanexpertevent->active = $this->POST('active');
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
