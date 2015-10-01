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
        $courses = $this->person->getCourses();
        $this->html .= "<table id='courses_table' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Title</th>
                        <th style='white-space:nowrap;'>Number</th>
                        <th style='white-space:nowrap;'>Catalog Description</th>
                        <th style='white-space:nowrap;'>USRIs</th>
                        <th style='white-space:nowrap;'>Start Date</th>
			<th style='white-space:nowrap;'>End Date</th></tr></thead><tbody>";
	foreach($courses as $course){
	    $this->html .= "<tr>";
	    $this->html .= "<td>{$course->subject}</td>";
	    $this->html .= "<td>{$course->catalog}</td>";
            $this->html .= "<td>{$course->courseDescr}</td>";
	    $this->html .= "<td></td>";
	    $this->html .= "<td style='white-space:nowrap;'>{$course->getStartDate()}</td>";
	    $this->html .= "<td style='white-space:nowrap;'>{$course->getEndDate()}</td>";

	}
        $this->html .= "</table></tbody><script type='text/javascript'>
                        $('#courses_table').dataTable();
        </script>";
    }
}
?>


