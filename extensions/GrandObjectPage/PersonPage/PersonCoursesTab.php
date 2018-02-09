<?php

class PersonCoursesTab extends AbstractTab {

    var $person;
    var $visibility;
    var $startRange;
    var $endRange;

    function PersonCoursesTab($person, $visibility, $startRange="0000-00-00", $endRange=CYCLE_END){
        parent::AbstractTab("Teaching");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->tooltip = "Contains a list of courses (and their corresponding student enrolments) that the faculty member has taught between the specified start and end dates.";
    }
    
    function getHTML($start=null, $end=null, $showPercentages=false, $generatePDF=false){
        if($start == null || $end == null){
            $courses = $this->person->getCourses();
        }
        else{
            $courses = $this->person->getCoursesDuring($start, $end);
        }
        $coursesArray = array();
        foreach($courses as $course){
            if($course->totEnrl > 0){
                $level = substr($course->catalog, 0, 1)."00";
                $coursesArray[$level]["{$course->subject} {$course->catalog}"][$course->getTerm()][] = $course;
            }
        }
        $item = "<small><i>Total enrolment per across multiple LEC, SEM, or LAB given in parentheses</i></small>";
        foreach($coursesArray as $level => $levels){
            $item .= "<h3>{$level} Level</h3>";
            $item .= "<table class='wikitable' frame='box' rules='all' width='100%'>";
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
                            $percents[$component][] = "{$percent}%";
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
                    $termStrings[] = "<b>{$term}</b>: $inner";
                }
                if(!$generatePDF){
                    $item .= @"<tr>
                                  <td style='white-space:nowrap;width:10%;' rowspan='2'>{$subj}";
                    if($nLec > 0){
                        $item .= "<br />&nbsp;&nbsp;#LEC: {$nLec}";
                    }
                    if($nLab > 0){
                        $item .= "<br />&nbsp;&nbsp;#LAB: {$nLab}";
                    }
                    if($nSem > 0){
                        $item .= "<br />&nbsp;&nbsp;#SEM: {$nSem}";
                    }
                    $item .= "</td>
                                  <td>{$terms[0]->descr}</td>
                                  <td style='width:45%;'>".implode("; ", $termStrings)."</td>
                              </tr>
                              <tr>
                                  <td colspan='2'><div class='pdfnodisplay'>{$terms[0]->courseDescr}</div></td>
                              </tr>";
                }
                else{
                    $item .= @"<tr>
                                  <td style='white-space:nowrap;width:10%;'>{$subj}
                                    ";
                    if($nLec > 0){
                        $item .= "<br />&nbsp;&nbsp;#LEC: {$nLec}";
                    }
                    if($nLab > 0){
                        $item .= "<br />&nbsp;&nbsp;#LAB: {$nLab}";
                    }
                    if($nSem > 0){
                        $item .= "<br />&nbsp;&nbsp;#SEM: {$nSem}";
                    }
                    $item .= "</td>
                                  <td>{$terms[0]->descr}</td>
                                  <td style='width:45%;'>".implode("; ", $termStrings)."</td>
                              </tr>";
                }
            }
            $item .= "</table>";
        }
        return $item;
    }

    function generateBody(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return "";
        }

        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$this->startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$this->endRange}' size='10' /></td>
                                <td><input type='button' value='Update' /></td>
                            </tr>
                        </table>
                        <script type='text/javascript'>
                            $('div#{$this->id} input[type=datepicker]').datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                yearRange: '1900:".(date('Y')+3)."',
                                onChangeMonthYear: function (year, month, inst) {
                                    var curDate = $(this).datepicker('getDate');
                                    if (curDate == null)
                                        return;
                                    if (curDate.getYear() != year || curDate.getMonth() != month - 1) {
                                        curDate.setYear(year);
                                        curDate.setMonth(month - 1);
                                        while(curDate.getMonth() != month -1){
                                            curDate.setDate(curDate.getDate() - 1);
                                        }
                                        $(this).datepicker('setDate', curDate);
                                        $(this).trigger('change');
                                    }
                                }
                            });
                            $('div#{$this->id} input[type=button]').click(function(){
                                var startRange = $('div#{$this->id} input[name=startRange]').val();
                                var endRange = $('div#{$this->id} input[name=endRange]').val();
                                document.location = '{$this->person->getUrl()}?tab={$this->id}&startRange=' + startRange + '&endRange=' + endRange;
                            });
                        </script>
                        </div>";
        $this->html .= $this->getHTML($this->startRange, $this->endRange, true);
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
