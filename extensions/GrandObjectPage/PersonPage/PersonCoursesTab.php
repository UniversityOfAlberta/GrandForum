<?php

class PersonCoursesTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonCoursesTab($person, $visibility){
        parent::AbstractTab("Courses");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $courses = $this->person->getCourses();
        $this->html .= "<table id='courses_table' frame='box' rules='all'>
                        <thead><tr>
                            <th style='white-space:nowrap;'>Title</th>
                            <th>Term</th>
                            <th style='white-space:nowrap;'>Title</th>
                            <th style='white-space:nowrap;'>Catalog Description</th>
                            <th style='white-space:nowrap;'>Start Date</th>
                            <th style='white-space:nowrap;'>End Date</th>
                            <th style='white-space:nowrap;'>User Comments</th>

                        </tr></thead><tbody>";
        foreach($courses as $course){
	    $user_id = $this->person->getId();
	    $course_id = $course->getId();
	    $comments = $course->getUserComment($user_id);
            $this->html .= "<tr>";
            $this->html .= "<td style='white-space: nowrap;'><a href='".$course->getUrl()."'>{$course->subject} {$course->catalog} ({$course->component})</a></td>";
            $this->html .= "<td>{$course->getTerm()}</td>";
            $this->html .= "<td>{$course->descr}</td>";
            $this->html .= "<td>{$course->courseDescr}</td>";
            $this->html .= "<td style='white-space:nowrap;'>{$course->getStartDate()}</td>";
            $this->html .= "<td style='white-space:nowrap;'>{$course->getEndDate()}</td>";
            $this->html .= "<td>$comments</td>";
	    $this->html .= "</tr>";

        }
        $this->html .= "</table></tbody><script type='text/javascript'>
                        $('#courses_table').dataTable({autoWidth: false, 'iDisplayLength': 25, 'aaSorting':[[0, 'asc'],[1,'asc'],[4,'desc']]});
        </script>";
    }
}
?>
