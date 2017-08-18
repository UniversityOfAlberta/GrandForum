<?php

class PersonCoursesReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        
        $tab = new PersonCoursesTab($person, array());
        return $tab->getHTML($start, $end);
        
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
                    $item .= "<h3>{$subj} - {$terms[0]->descr}</h3><p>{$terms[0]->courseDescr}</p><ul>";
                }   
                $courses = new Collection($terms);
                $components = $courses->pluck('component');
                $sects = $courses->pluck('sect');
                $totEnrls = $courses->pluck('totEnrl');
                
                $inner = array();
                foreach($components as $key => $component){
                    $inner[] = "{$component} {$sects[$key]} : {$totEnrls[$key]}";
                }
                $inner = implode(", ", $inner);
                $item .= "<li><b>{$term}</b> ($inner)</li>";
                $first = false;
            }
            $item .= "</ul>";
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
