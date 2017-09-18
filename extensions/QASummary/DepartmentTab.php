<?php

class DepartmentTab extends AbstractTab {

    var $department;
    var $depts;

    function DepartmentTab($department, $depts){
        parent::AbstractTab($depts[0]);
        $this->department = $department;
        $this->depts = $depts;
    }
    
    function generateBody(){
        global $wgOut;
        $year = YEAR;
        $people = array();
        foreach(Person::getAllPeople(NI) as $person){
            foreach($person->getUniversitiesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $uni){
                if($uni['department'] == $this->department){
                    $people[$person->getId()] = $person;
                }
            }
        }
        $courses = array();
        $merged = array();
        foreach($this->depts as $dept){
            $merged = array_merge($merged, Course::newFromSubjectCatalog($dept, ""));
        }
        foreach($merged as $course){
            $courses["{$course->subject} {$course->catalog}"][] = $course;
        }
        
        ksort($courses);
        $html = "<h1>Department of Chemistry</h1>";
        $html .= "<h2>Course Descriptions and Associated Instructors</h2>";
        foreach($courses as $key => $course){
            $html .= "<h3>{$key}</h3>";
            $html .= "<p>{$course[0]->courseDescr}</p>";
            $html .= "<b>Instructors:</b> ";
            $profs = array();
            foreach($course as $c){
                $professors = $c->getProfessors();
                foreach($professors as $prof){
                    $profs[$prof->getId()] = "<a href='{$prof->getUrl()}'>{$prof->getReversedName()}</a>";                
                }
            }
            $html .= implode("; ", $profs);
        }
        
        $html .= "<h2>Distribution of Courses across Instructors</h2>";
        $html .= "<table>";
        foreach($people as $person){
            $html .= "<tr><td>{$person->getReversedName()}</td>";
            $personCourses = array();
            foreach($person->getCoursesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $course){
                if(in_array($course->subject, $this->depts)){
                    $personCourses["{$course->subject} {$course->catalog}"] = "{$course->subject} {$course->catalog}";
                }
            }
            ksort($personCourses);
            $html .= "<td>".implode(", ", $personCourses)."</td></tr>";
        }
        $html .= "</table>";
        
        $this->html .= $html;
        
    }

}
?>
