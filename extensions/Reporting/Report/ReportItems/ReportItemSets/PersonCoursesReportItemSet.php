<?php

class PersonCoursesReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $term = $this->getAttr('term', '');
        $unique = strtolower($this->getAttr('unique', 'false'));
        $component = $this->getAttr('component', '');
        $courses = $person->getCoursesDuring($start, $end);
        $alreadyDone = array();
        if(is_array($courses)){
            foreach($courses as $course){
                if(($term == '' || $course->term_string == $term) &&
                   ($component == '' || strstr($component, $course->component) !== false)){
                    if($unique && isset($alreadyDone[$course->subject.$course->catalog])){
                        continue;
                    }
                    $tuple = self::createTuple();
                    $tuple['project_id'] = $course->id;
                    $tuple['extra'] = $course->toArray();
                    $data[] = $tuple;
                    $alreadyDone[$course->subject.$course->catalog] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
