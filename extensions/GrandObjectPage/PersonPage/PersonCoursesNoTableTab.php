<?php

class PersonCoursesNoTableTab extends PersonCoursesTab {

    function PersonCoursesNoTableTab($person, $visibility, $startRange="0000-00-00", $endRange=CYCLE_END){
        parent::PersonCoursesTab($person, $visibility, $startRange, $endRange);
    }
    
    function getHTML($start=null, $end=null, $showPercentages=false, $generatePDF=false, $editing=false){
        $coursesArray = $this->getArray($start, $end);
        $item = "";
        if($this->levels == null){
            $item .= "<small><i>Total enrolment per course across multiple LEC, SEM, or LAB given in parentheses.";
            if($showPercentages){
                $item .= "  Teaching percentages in square brackets.";
            }
            $item .= "</i></small>";
        }
        foreach($coursesArray as $level => $levels){
            if($this->levels == null){
                $item .= "<h3>{$level} Level</h3>";
            }
            foreach($levels as $subj => $terms){
                $termStrings = array();
                $nLec = 0;
                $nLab = 0;
                $nSem = 0;
                foreach($terms as $term => $terms){
                    $courses = new Collection($terms);
                    $components = $courses->pluck('component');
                    $ids = $courses->pluck('id');
                    $sects = $courses->pluck('sect');
                    $totEnrls = $courses->pluck('totEnrl');
                    
                    $inner = array();
                    $counts = array();
                    $percents = array();
                    foreach($components as $key => $component){
                        $counts[$component][] = $totEnrls[$key];
                        $percent = $this->person->getCoursePercent($ids[$key]);
                        if($percent != ""){
                            $percents[$component][$ids[$key]] = "{$percent}%";
                        }
                        switch($component){
                            case "LEC":
                                $nLec++;
                                break;
                            case "LAB":
                                $nLab++;
                                break;
                            case "SEM":
                                $nSem++;
                                break;
                        }
                    }
                    foreach($counts as $component => $count){
                        $percentages = "";
                        if($showPercentages && isset($percents[$component])){
                            $percentages .= " [";
                            $percentages .= @implode(",", $percents[$component]);
                            $percentages .= "]";
                        }
                        $inner[] = count($count)." {$component}{$percentages} (".array_sum($count).")";
                    }
                    $inner = implode(", ", $inner);
                    $termStrings[] = "{$term}: $inner";
                }
                $counts = array();
                if($nLec > 0){
                    $counts[] = "#LEC: {$nLec}";
                }
                if($nLab > 0){
                    $counts[] = "#LAB: {$nLab}";
                }
                if($nSem > 0){
                    $counts[] = "#SEM: {$nSem}";
                }
                $item .= @"<b>{$subj} {$terms[0]->descr} (".implode(", ", $counts).")</b> ".implode("; ", $termStrings)."<br />";
            }
        }
        return $item;
    }
    
}
?>
