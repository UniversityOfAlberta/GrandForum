<?php

class PersonCoursesReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $term = $this->getAttr('term', '');
        $unique = strtolower($this->getAttr('unique', 'false'));
        $exclude13Week = strtolower($this->getAttr('exclude13Week', 'false'));
        $component = $this->getAttr('component', '');
        if($term == ''){
            $courses = $person->getCourses($start, $end);
        }
        else{
            $courses = $person->getCourses();
        }
        $alreadyDone = array();
        if(is_array($courses)){
            foreach($courses as $course){
                if(($term == '' || strstr($term, $course->term_string) !== false) &&
                   ($component == '' || $component == $course->component)){
                    if($unique && isset($alreadyDone[$course->subject.$course->catalog])){
                        continue;
                    }
                    if($exclude13Week && strstr($course->term_string, "Spring") !== false && strstr($course->catalog, "A") !== false){
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
