<?php

class PersonCoursesReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $courses = $person->getCoursesDuring($start, $end);
        $coursesArray = array();
        foreach($courses as $course){
            if($course->totEnrl > 0){
                $coursesArray["{$course->subject} {$course->catalog}"][$course->getTerm()][] = $course;
            }
        }
        $item = "";
        foreach($coursesArray as $subj => $terms){
            $first = true;
            foreach($terms as $term => $terms){
                if($first){
                    $item .= "<h3>{$subj} - {$terms[0]->descr}</h3>
                      <table class='wikitable' rules='all' frame='box' width='100%'>
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Comp.</th>
                                <th>Section</th>
                                <th>Enroll.</th>
                            </tr>
                        </thead>
                        <tbody>";
                }
                $courses = new Collection($terms);
                $components = $courses->pluck('component');
                $sects = $courses->pluck('sect');
                $totEnrls = $courses->pluck('totEnrl');
                
                $item .= "<tr>
                              <td align='center'>{$course->getTerm()}</td>
                              <td align='center'>".implode("<br />", $components)."</td>
                              <td align='center'>".implode("<br />", $sects)."</td>
                              <td align='center'>".implode("<br />", $totEnrls)."</td>
                          </tr>";
                $first = false;
            }
            $item .= "</tbody>
                    </table>";
        }
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
