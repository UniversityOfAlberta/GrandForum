<?php

class PersonCoursesReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $courses = $person->getCoursesDuring($start, $end);
        
        $item = "<table class='wikitable' rules='all' frame='box' width='100%'>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Title</th>
                            <th>Term</th>
                            <th>Comp.</th>
                            <th>Section</th>
                            <th>Enroll.</th>
                        </tr>
                    </thead>
                    <tbody>";
        $coursesArray = array();
        foreach($courses as $course){
            $coursesArray["{$course->subject} {$course->catalog}"][$course->getTerm()][] = $course;
        }
        foreach($coursesArray as $subj => $terms){
            $nRows = array_sum(array_map("count", $terms));
            
            $first1 = true;
            foreach($terms as $term => $terms){
                $first2 = true;
                foreach($terms as $course){
                    $item .= "<tr>";
                    if($first1){
                        $item .= "<td align='center' rowspan='$nRows'>{$course->subject} {$course->catalog}</td>
                                  <td align='left' rowspan='$nRows'>{$course->descr}</td>";
                    }
                    if($first2){
                        $item .= "<td align='center' rowspan='".count($terms)."'>{$course->getTerm()}</td>";
                    }
                    $item .= "<td align='center'>{$course->component}</td>
                              <td align='center'>{$course->sect}</td>
                              <td align='center'>{$course->totEnrl}</td>
                          </tr>";
                    $first1 = false;
                    $first2 = false;
                }
            }
        }
        $item .= "</tbody>
                </table>";
        return $item;
    }

    function render(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
