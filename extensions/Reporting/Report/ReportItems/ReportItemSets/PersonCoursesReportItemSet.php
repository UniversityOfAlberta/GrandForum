<?php

class PersonCoursesReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $courses = $person->getCourses();
	if(is_array($courses)){
            foreach($courses as $course){
                $tuple = self::createTuple();
                $tuple['project_id'] = $course->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
