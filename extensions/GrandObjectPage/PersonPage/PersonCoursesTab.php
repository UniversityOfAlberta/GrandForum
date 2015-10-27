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
                        <thead><tr><th style='white-space:nowrap;'>Title</th>
                        <th style='white-space:nowrap;'>Number</th>
                        <th style='white-space:nowrap;'>Catalog Description</th>
                        <th style='white-space:nowrap;'>USRIs</th>
                        <th style='white-space:nowrap;'>Start Date</th>
			<th style='white-space:nowrap;'>End Date</th></tr></thead><tbody>";
	foreach($courses as $course){
	    $courseEval = $this->person->getCourseEval($course->id);
	    $this->html .= "<tr>";
	    $this->html .= "<td>{$course->subject}</td>";
	    $this->html .= "<td>{$course->catalog}</td>";
            $this->html .= "<td>{$course->courseDescr}</td>";
	    $this->html .= "<td style='white-space:nowrap;'>";
	    if(isset($courseEval['evals'])){
                $this->html .= "<a href='#!' onclick='$(\"#dialog{$course->id}\").dialog({width:\"1100px\",position: { my: \"center\", at: \"center\", of: window }})'>Course Evaluation</a>
                                <div id='dialog{$course->id}' title='Course Evaluation for {$course->subject} {$course->catalog}' style='display:none;'>";
	        $this->html .= "<table class='dashboard wikitable'><thead>
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
	   	foreach($courseEval['evals'] as $question){
				    $this->html .= "<tr>
							<td style='white-space:nowrap;>{$question['Question']}</td>
							<td align=center>{$question['Strongly Disagree']}</td>
							<td align=center>{$question['Disagree']}</td>
							<td align=center>{$question['Neither D or A']}</td>
		                                        <td align=center>{$question['Agree']}</td>
                		                        <td align=center>{$question['Strongly Agree']}</td>
                                		        <td align=center>{$question['Median']}</td>
                                       			<td align=center>{$question['Tukey Fence']}</td>
							<td align=center>{$question['25%']}</td>
							<td align=center>{$question['50%']}</td>
							<td align=center>{$question['75%']}</td>
						    </tr>";

		}
	        $this->html .= "</tbody></table></div>";
	    }
	    else{
		$this->html .= "No Current Data";
	    }
	    $this->html .= "</td>";
            
	    $this->html .= "<td style='white-space:nowrap;'>{$course->getStartDate()}</td>";
	    $this->html .= "<td style='white-space:nowrap;'>{$course->getEndDate()}</td>";

	}
        $this->html .= "</table></tbody><script type='text/javascript'>
                        $('#courses_table').dataTable({'aaSorting':[[0, 'asc'],[1,'asc'],[4,'desc']]});
			
			
        </script>";
    }
}
?>
