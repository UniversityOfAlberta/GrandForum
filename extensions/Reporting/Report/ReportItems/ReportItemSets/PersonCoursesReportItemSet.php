<?php

class PersonCoursesReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $courses = $person->getCoursesDuring($start, $end);
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
