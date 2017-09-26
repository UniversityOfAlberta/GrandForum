<?php

/**
 * Class SopAPI
 * API Class for interacting with individual SOPs
 */
class CourseAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $course = Course::newFromId($this->getParam('id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
            }
            return $course->toJSON();
        }
    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
       $course = new Course(array());
        header('Content-Type: application/json');
        $course->subject = $this->POST('subject');
        $course->catalog = $this->POST('catalog');
        $course->component = $this->POST('component');
        $course->term = $this->POST('term');
        $course->startDate = $this->POST('startDate');
        $course->endDate = $this->POST('endDate');
        $course->courseDescr = $this->POST('courseDescr');
	$course->comments = $this->POST('course_comment');
        $status = $course->create();
        if(!$status){
            $this->throwError("The course could not be created");
        }
        $course = Course::newFromId($course->getId());
        return $course->toJSON();
    }

  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        $course = Course::newFromId($this->getParam('id'));
        if($course == null || $course->subject == ""){
            $this->throwError("This course does not exist");
        }
        header('Content-Type: application/json');
        $course->subject = $this->POST('subject');
        $course->catalog = $this->POST('catalog');
        $course->component = $this->POST('component');
        $course->term = $this->POST('term');
        $course->startDate = $this->POST('startDate');
        $course->endDate = $this->POST('endDate');
        $course->courseDescr = $this->POST('courseDescr');
        $status = $course->update();
        if(!$status){
            $this->throwError("The course could not be updated");
        }
        $course = Course::newFromId($this->getParam('id'));
        return $course->toJSON();
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
