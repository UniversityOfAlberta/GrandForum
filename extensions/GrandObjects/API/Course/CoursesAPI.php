<?php

/**
 * Class SopsAPI
 * API class for interacting wiht SOPs collection
 */
class CoursesAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
	
	$me = Person::newFromWgUser();
        if($me->isRoleAtLeast(ADMIN)){
		$courses = new Collection(Course::getAllCourses());
	}
	else{
	    $courses = new Collection(Course::getUserCourses());
	}
        return $courses->toJSON();
    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
        return false;
    }

  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        return false;
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
