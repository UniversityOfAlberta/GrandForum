<?php

class PersonCoursesTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonCoursesTab($person, $visibility){
        parent::AbstractTab("Teaching");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function getHTML($start=null, $end=null){
        if($start == null || $end == null){
            $courses = $this->person->getCourses();
        }
        else{
            $courses = $this->person->getCoursesDuring($start, $end);
        }
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

    function generateBody(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $this->html = $this->getHTML();
        return;
        $courses = $this->person->getCourses();
        $this->html .= "<table id='courses_table' frame='box' rules='all'>
                        <thead><tr>
                            <th style='white-space:nowrap;'>Title</th>
                            <th>Term</th>
                            <th style='white-space:nowrap;'>Title</th>
                            <th style='white-space:nowrap;'>Catalog Description</th>
                            <th style='white-space:nowrap;'>USRIs</th>
                            <th style='white-space:nowrap;'>Enrolled</th>
                            <th style='white-space:nowrap;'>Start Date</th>
                            <th style='white-space:nowrap;'>End Date</th>
                        </tr></thead><tbody>";
        foreach($courses as $course){
            $courseEval = $this->person->getCourseEval($course->id);
            $this->html .= "<tr>";
            $this->html .= "<td style='white-space:nowrap;'>{$course->subject} {$course->catalog} ({$course->component})</td>";
            $this->html .= "<td>{$course->getTerm()}</td>";
            $this->html .= "<td>{$course->descr}</td>";
            $this->html .= "<td>{$course->courseDescr}</td>";
            $this->html .= "<td style='white-space:nowrap;'>";
            if(isset($courseEval['evaluation'])){
                $month = $course->getStartMonth();
                $year = $course->getStartYear();
                $term = $course->getTermUsingStartMonth($month);
                $this->html .= "<a href='#!' onclick='$(\"#dialog{$course->id}\").dialog({width:\"1100px\",position: { my: \"center\", at: \"center\", of: window }})'>Course Evaluation</a>
                                <div id='dialog{$course->id}' title='Course Evaluation for {$course->subject} {$course->catalog} {$term} {$year}' style='display:none;'>";
                $this->html .= "Processed on <i>{$courseEval['month']} {$courseEval['day']}, {$courseEval['year']}</i>
                <br /><br /><table class='dashboard wikitable'><thead>
                    <tr>
                    <th rowspan=2>Question</th>
                    <th rowspan=2>Strongly Disagree</th>
                    <th rowspan=2>Disagree</th>
                    <th rowspan=2>Neither D or A</th>
                    <th rowspan=2>Agree</th>
                    <th rowspan=2>Strongly Agree</th>
                    <th rowspan=2>Median</th>
                    <th rowspan=2>Tukey Fence</th>
                    <th colspan=3>Reference Data</th>
                    </tr>
                    <tr><th>25%</th>
                    <th>50%</th>
                    <th>75%</th>
                    </tr>
                    </thead>
                    <tbody>";
                foreach($courseEval['evaluation'] as $question){
                    $this->html .= "<tr>
                            <td style='white-space:nowrap;'>{$question['question']}</td>
                            <td align=center>{$question['strongly disagree']}</td>
                            <td align=center>{$question['disagree']}</td>
                            <td align=center>{$question['neither d or a']}</td>
                            <td align=center>{$question['agree']}</td>
                            <td align=center>{$question['strongly agree']}</td>
                            <td align=center>{$question['median']}</td>
                            <td align=center>{$question['tukey fence']}</td>
                            <td align=center>{$question['25%']}</td>
                            <td align=center>{$question['50%']}</td>
                            <td align=center>{$question['75%']}</td>
                            </tr> ";

                }
                $this->html .= "</tbody></table></div>";
            }
            else{
                $this->html .= "No Current Data";
            }
            $this->html .= "</td>";
            $this->html .= "<td>{$course->totEnrl}</td>";
            $this->html .= "<td style='white-space:nowrap;'>{$course->getStartDate()}</td>";
            $this->html .= "<td style='white-space:nowrap;'>{$course->getEndDate()}</td>";

        }
        $this->html .= "</table></tbody><script type='text/javascript'>
                        $('#courses_table').dataTable({autoWidth: false, 'iDisplayLength': 25, 'aaSorting':[[0, 'asc'],[1,'asc'],[4,'desc']]});
        </script>";
    }
}
?>
