<?php
    require_once( "commandLine.inc" );
    function convertToDays($date){
      	$str = "January 05 2009";
      	$str = (strtotime($date)) - strtotime("Jan 01 1900");
	return floor($str/3600/24);
    }
    $wgUser=User::newFromName("Admin");
    $person = Person::newFromName("Eleni Stroulia");
    $url = explode("\n",file_get_contents('http://grand.cs.ualberta.ca/~ruby/index.html'));
    $sd = file_get_contents('http://grand.cs.ualberta.ca/~ruby/index.html');
    $sd= preg_replace("!\s+!", " ", $sd);
    $regex = '/were processed on (.+?)\./';
    preg_match_all($regex, $sd, $array);
    $dates = $array[1];
    $regex = '/\<h3\>(.+?)\<\/h3\>/';
    preg_match_all($regex, $sd, $array);
    $courses = $array[1];
    $coursesinfo = array();
    foreach($courses as $course){
	$info = explode("&nbsp;&nbsp;", $course);
	$coursesinfo[] = $info;
    }
    $regex = '/\<TABLE(.+?)\>(.+?)\<\/TABLE\>/';
    preg_match_all($regex, $sd, $array);
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
print_r($finalArray);
//------------ENTERING DATA-----------//
  /* foreach($finalArray as $course){
	$person = Person::newFromNameLike($course['user']);
	$userCourses = $person->getCourses();
	$start_date = $course['end_date'];
	$year = date("Y", strtotime($start_date));
	$month = date("M", strtotime($start_date));
   	if((strtotime("March")) <= strtotime($month) && strtotime($month) <=(strtotime("August"))){
	    $date = "January 05 ".$year;
	}
	elseif($month == "Jan"){
	   $date = "September 05 ".($year-1);
	}
	else{
	   $date = "September 05 ".$year;
	}
	print_r($start_date."-->".$date." ");
	$coursename = explode(" ",$course['course_name']);
	$subject = $coursename[0];
	$catalog = $coursename[1];
	$userC = "";
	print_r($catalog."\n"); 
	$days = convertToDays($date);
	foreach($userCourses as $userCourse){
	    if($subject == $userCourse->subject && $catalog == $userCourse->catalog && $days == $userCourse->startDate){
		$courseId = $userCourse->id;
		DBFunctions::update('grand_user_courses',
					      array('course_evals' => serialize($course)),
                                      		array('course_id' => EQ($courseId),
						      'user_id' => EQ($person->getId())),
						array(),
						true);
		break;
		
	    }
	} 
   }
*/


?>

