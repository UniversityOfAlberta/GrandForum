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
        $year = YEAR-1;
        $people = array();
        $hqps = array();
        $ugrads = array();
        $masters = array();
        $phds = array();
        $techs = array();
        $pdfs = array();
        foreach(Person::getAllPeople(NI) as $person){
            foreach($person->getUniversitiesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $uni){
                if($uni['department'] == $this->department){
                    $people[$person->getId()] = $person;
                    break;
                }
            }
        }
        foreach(Person::getAllPeople(HQP) as $person){
            foreach($person->getUniversitiesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $uni){
                if($uni['department'] == $this->department){
                    $hqps[$person->getId()] = $person;
                    if(in_array(strtolower($uni['position']), array("ugrad", "undergraduate", "undergraduate student"))){
                        $ugrads[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("msc", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s"))){
                        $masters[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("phd", "phd student", "graduate student - doctoral"))){
                        $phds[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("technician", "ra", "research/technical assistant", "professional end user"))){
                        $techs[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("pdf","post-doctoral fellow"))){
                        $pdfs[$person->getId()] = $person;
                    }
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
        $html = "<h1>Department of {$this->department}</h1>";
        $html .= "<h2>Quality Assurance Reporting Period: July 1, ".($year-5)." - June 30, $year</h2>";
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
        
        $gradPapers = array();
        $ugradPapers = array();
        
        foreach($hqps as $hqp){
            $papers = $hqp->getPapersAuthored("all", ($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
            foreach($papers as $paper){
                $uni = $hqp->getUniversityDuring($paper->getDate(), $paper->getDate());
                $pos = @$uni['position'];
                if(in_array(strtolower($pos), array("phd","msc","phd student", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s", "graduate student - doctoral", "pdf","post-doctoral fellow"))){
                    $gradPapers[$paper->getId()] = $paper;
                }
                else if(in_array(strtolower($pos), array("ugrad", "undergraduate", "undergraduate student"))){
                    $ugradPapers[$paper->getId()] = $paper;
                }
            }
        }
        
        $html .= "<h2>Graduate Student Publications</h2>";
        $html .= "<p>Total # of publications: ".count($gradPapers)."</p>";
        $html .= "<small>Graduate student name boldfaced</small><br />";
        $html .= "<ul>";
        foreach($gradPapers as $paper){
            $html .= "<li>{$paper->getCitation()}</li>";
        }
        $html .= "</ul>";
        
        $html .= "<h2>Undergradate Student Publications</h2>";
        $html .= "<p>Total # of publications: ".count($ugradPapers)."</p>";
        $html .= "<small>Graduate student name boldfaced</small><br />";
        $html .= "<ul>";
        foreach($ugradPapers as $paper){
            $html .= "<li>{$paper->getCitation()}</li>";
        }
        $html .= "</ul>";
        
        $html .= "<h2>HQP Supervision Summary Document</h2>";
        
        $html .= "<p>Total number of undergraduate students: ".count($ugrads)."</p>";
        
        $html .= "<p>Total number of MSc students: ".count($masters)."</p>";
        
        $html .= "<p>Total number of  PhD students: ".count($phds)."</p>";
        
        $html .= "<p>Total number of technicians: ".count($techs)."</p>";
        
        $html .= "<p>Total number of PDFs: ".count($pdfs)."</p>";
        
        $this->html .= $html;
        
    }

}
?>
