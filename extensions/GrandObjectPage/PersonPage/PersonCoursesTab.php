<?php

class PersonCoursesTab extends AbstractEditableTab {

    var $person;
    var $visibility;
    var $startRange;
    var $endRange;
    var $levels = null; // array(1,2,3,4) to specify course levels

    function __construct($person, $visibility, $startRange="0000-00-00", $endRange=CYCLE_END){
        parent::__construct("Teaching");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->tooltip = "Contains a list of courses (and their corresponding student enrolments) that the faculty member has taught between the specified start and end dates.";
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($this->person->isMe() || $me->isRoleAtLeast(MANAGER));
    }
    
    function handleEdit(){
        if(isset($_POST['percentages']) && is_array($_POST['percentages'])){
            foreach($_POST['percentages'] as $key => $percent){
                if(!is_numeric($percent)){
                    $percent = 100;
                }
                $percent = max(0, min(100, $percent));
                DBFunctions::update('grand_user_courses',
                                    array('percentage' => $percent),
                                    array('user_id' => $this->person->getId(),
                                          'course_id' => $key));
            }
        }
    }
    
    function getArray($start=null, $end=null){
        if($start == null || $end == null){
            $courses = $this->person->getCourses();
        }
        else{
            $courses = $this->person->getCoursesDuring($start, $end);
        }
        $coursesArray = array();
        foreach($courses as $course){
            $level = substr($course->catalog, 0, 1);
            if(count($this->levels) > 0){
                // Exceptions
                if($course->subject == "MED" && ($course->catalog == "521" || $course->catalog == "525")){ $level = "1"; }
            }
            if($course->totEnrl > 0 && ($this->levels == null || in_array($level, $this->levels))){
                $level = "{$level}00";
                $coursesArray[$level]["{$course->subject} {$course->catalog}"][$course->getTerm()][] = $course;
            }
        }
        return $coursesArray;
    }
    
    function getHTML($start=null, $end=null, $showPercentages=false, $generatePDF=false, $editing=false){
        $coursesArray = $this->getArray($start, $end);
        $item = "";
        if($editing){
            $item .= "<p>You can edit the teaching percentages for each course section below.  To exclude it from your annual report you can enter '0' in the percent field.</p>";
        }
        if($this->levels == null){
            $item .= "<small><i>Total enrolment per course across multiple LEC, SEM, or LAB given in parentheses.";
            if($showPercentages){
                $item .= "  Teaching percentages in square brackets.";
            }
            $item .= "</i></small>";
        }
        if($this->levels != null){
            $item .= "<table class='wikitable' frame='box' rules='all' width='100%'>";
        }
        foreach($coursesArray as $level => $levels){
            if($this->levels == null){
                $item .= "<h3>{$level} Level</h3>";
                $item .= "<table class='wikitable' frame='box' rules='all' width='100%'>";
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
                            if($editing){
                                $inputs = array();
                                foreach($percents[$component] as $key => $percent){
                                    $percent = str_replace("%", "", $percent);
                                    $sect = $sects[array_search($key, $ids)];
                                    $inputs[] = "{$sect}:<input type='text' style='height:10px; width: 25px;' name='percentages[$key]' value='{$percent}' />%";
                                }
                                $percentages .= @implode(", ", $inputs);
                            }
                            else{
                                $percentages .= @implode(", ", $percents[$component]);
                            }
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
            if($this->levels == null){
                $item .= "</table>";
            }
        }
        if($this->levels != null){
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
    }
    
    function generateEditBody(){
        global $wgUser;
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
        $this->html .= $this->getHTML($this->startRange, $this->endRange, true, false, true);
    }
}
?>
