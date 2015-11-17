<?php
    class UploadCourseEvalsAPI extends API{
	var $evals = array();
	function processParams($params){
	    //TODO
	}

	function parseDate($start_date){
                $year = date("Y", strtotime($start_date));
                $month = date("M", strtotime($start_date));
                if((strtotime("March")) <= strtotime($month) && strtotime($month) <=(strtotime("August"))){
                   $date = "January 05 ".$year;
                }
                else if($month == "Jan"){
                   $date = "September 05 ".($year-1);
                }
                else{
                   $date = "September 05 ".$year;
                }
                return $date;
	}

       function getTermDays($date){
           $first_date = new DateTime("1900-01-01 00:00:00");
           $second_date = new DateTime($date);
           $difference = $first_date->diff($second_date)->format("%a");
           return $difference;
       }

	function setEvalInfo($eval_content){
	    $eval_content= preg_replace("!\s+!", " ", $eval_content);
	    $regex = '/were processed on (.+?)\./';
	    preg_match_all($regex, $eval_content, $array);
	    $dates = $array[1];
    	    $regex = '/\<h3\>(.+?)\<\/h3\>/';
	    preg_match_all($regex, $eval_content, $array);
	    $courses = $array[1];
	    $coursesinfo = array();
	    foreach($courses as $course){
           	$info = explode("&nbsp;&nbsp;", $course);
        	$coursesinfo[] = $info;
    	    }
	    $regex = '/\<TABLE(.+?)\>(.+?)\<\/TABLE\>/';
	    preg_match_all($regex, $eval_content, $array);
	    $tables = $array[2];
	    $courseEvals = array();
	    $headers = array();
	    foreach($tables as $table){
        	$evals = array();
        	$regex = '/\<tr\>(.+?)\<\/tr\>/';
        	preg_match_all($regex, $table, $array);
        	$rows = $array[1];
        	$header = $rows[0].$rows[1];
        	$regex = '/\<small\>(.+?)\<\/small\>/';
        	preg_match_all($regex, $header, $array);
        	$headers[] = $array[1];
        	for($i=2;$i<count($rows);$i++){
           	    $regex = '/\<td(.+?)\>(.+?)\<\/td\>/';
           	    preg_match_all($regex, $rows[$i], $array);
           	    $evals[] = $array[2];
        	}
        	$courseEvals[] = $evals;
    	    }
   	    $finalArray = array();
   	    for($i=0;$i<count($coursesinfo);$i++){
        	$course = array();
        	$course['user'] = trim(strtolower($coursesinfo[$i][0]));
        	$course['course_name'] = trim($coursesinfo[$i][1]);
        	$course['end_date'] = trim($dates[$i]);
        	$questions = array();
            	foreach($courseEvals[$i] as $question){
            	    $flag = false;
            	    $questionArray = array();
            	    for($n=0;$n<count($headers[$i])-1;$n++){
                	$header = $headers[$i][$n];
                	if($header === 'Reference Data'){
                            $flag = true;
                	}
                	if($flag){
                     	    $header = $headers[$i][$n+1];
                      	    $header = trim(str_replace(array("<br>","<small>","</small>"), " ", $header));
                    	    $header = preg_replace("!\s+!", " ", $header);
                    	    $questionArray[$header] = $question[$n];
                	}
                	$header = trim(str_replace(array("<br>","<small>","</small>"), " ", $header));
                	$header = preg_replace("!\s+!", " ", $header);
                	$questionArray[$header] = trim(str_replace(array("<br>","<small>","</small>"), " ",$question[$n]));
            	    }
            	    $questions[] = $questionArray;
        	}
        	$course['evals'] = $questions;
        	$finalArray[] = $course;
   	    }
	    $this->evals = $finalArray;
	}

	function createEvalInfo($person, $evaluations){
	    foreach($evaluations as $evaluation){
        	//$person = Person::newFromNameLike($course['user']);
		$userCourses = $person->getCourses();
        	$evaluation['start_date'] = $this->getTermDays($this->parseDate($evaluation['end_date']));
        	$coursename = explode(" ",$evaluation['course_name']);
        	$evaluation['subject'] = $coursename[0];
        	$evaluation['catalog'] = $coursename[1];
		$course = Course::newFromSubjectCatalogSectStartDateTermLike($evaluation['subject'], $evaluation['catalog'], '%', $evaluation['start_date'], '%');
            	if($course->getId() == ""){
		    $course = new Course(array());
		    $course->subject = $evaluation['subject'];
		    $course->catalog = $evaluation['catalog'];
		    $course->startDate = $evaluation['start_date'];
		    $course->endDate = $this->getTermDays($evaluation['end_date']);
		    $course->create();
		    $course = Course::newFromSubjectCatalogSectStartDateTermLike($evaluation['subject'], $evaluation['catalog'], '%', $evaluation['start_date'], '%');
		}
		$found = false;
		foreach($userCourses as $userCourse){
		    if($evaluation['subject'] == $userCourse->subject && $evaluation['catalog'] == $userCourse->catalog &&
			$evaluation['start_date'] == $userCourse->startDate){
			$found = true;
			break;
		    }
		}
		if(!$found){
		    DBFunctions::insert('grand_user_courses',
					array('course_id' => $course->getId(),
					      'user_id' => $person->getId()),
					true);
		}
                DBFunctions::update('grand_user_courses',
                                      array('course_evals' => serialize($evaluation)),
                                        array('course_id' => EQ($course->getId()),
                                              'user_id' => EQ($person->getId())),
                                        array(),
                                        true);
	    }
	    return $evaluation['subject'];
	}

	function doAction($noEcho=false){
	    global $wgMessage;
	    $me = Person::newFromWgUser();
	    if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
	   	$person = Person::newFromId($_POST['id']);
	    }
	    else{
		$person = $me;
	    }
	    $eval_file = $_FILES['eval'];
	    if($eval_file['type'] == 'text/html' &&
	        $eval_file['size'] > 0){
		$file_contents = file_get_contents($eval_file['tmp_name']);
		$this->setEvalInfo($file_contents);
		if(count($this->evals)){
		    $hi = $this->createEvalInfo($person, $this->evals);
		}
		echo <<<EOF
            	<html>
                    <head>
                    	<script type='text/javascript'>
                                            parent.ccvUploaded([], "$hi");
                    	</script>
                    </head>
            	</html>
EOF;
            	exit;
	    }
	    else{
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "The uploaded files were not in .xls format");
                    </script>
                </head>
            </html>
EOF;
	    exit;
	    }
	}	
	
	function isLoginRequired(){
	    return true;
	}
    }
?>
