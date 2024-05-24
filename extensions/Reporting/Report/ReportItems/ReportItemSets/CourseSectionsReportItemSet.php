<?php

class CourseSectionsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $course = Course::newFromId($this->projectId);
        $person = Person::newFromId($this->personId);
        $ids = DBFunctions::execSQL("SELECT c.id
                                      FROM  `grand_courses` c, `grand_user_courses` uc
                                      WHERE c.id = uc.course_id
                                      AND uc.user_id = '{$person->getId()}'
                                      AND c.term_string = '{$course->term_string}'
                                      AND c.subject = '{$course->subject}'
                                      AND c.catalog = '{$course->catalog}'
                                      AND uc.percentage != '0'");
                                      
        foreach($ids as $row){
            $course = Course::newFromId($row['id']);
            $tuple = self::createTuple();
            $tuple['project_id'] = $course->id;
            $tuple['extra'] = $course->toArray();
            $data[] = $tuple;
        }
        return $data;
    }

}

?>
