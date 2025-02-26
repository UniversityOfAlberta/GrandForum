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
                   $date = "January 09 ".$year;
                }
                else if($month == "Jan"){
                   $date = "September 01 ".($year-1);
                }
                else{
                   $date = "September 01 ".$year;
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
            $string = $eval_content;
            $DOM = new DOMDocument;
            $DOM->loadHTML($string);

//--------------------------------------------------//
                    $prof_tables = $DOM->getElementsByTagName('table');
                    $prof_arrays = array();
                    for ($i = 1; $i < $prof_tables->length; $i=$i+6){
                            $prof_arrays[] = $prof_tables->item($i);
                    }
            //print_r($prof_arrays);
            
            
            $eval_tables = $DOM->getElementsByTagName('table');
            $eval_arrays = array();
            for ($i = 2; $i < $eval_tables->length; $i=$i+6){
                $eval_arrays[] = $eval_tables->item($i);
            }
            //print_r($eval_arrays);
            $overall_arrays = array();
            for($i = 0; $i < count($eval_arrays); $i++){
                $overall_arrays[] = array("prof" => $prof_arrays[$i],
                                          "eval" => $eval_arrays[$i]);
            }
            //print_r($overall_arrays);
            $course_evaluations = array();
            //-----------------------------------------------------//
            foreach($overall_arrays as $overall_array){
                    $prof_table = $overall_array['prof'];
                    $prof_info = $prof_table->getElementsByTagName('td');
                    $new_prof_array = array();
                    for ($i = 0; $i < $prof_info->length; $i++){
                        $new_prof_array[] = $prof_info->item($i)->nodeValue;
                    }
                    //print_r($new_prof_array);
            
            
            
            
                    $prof_information = explode(" ", preg_replace('/\s+/', ' ',$new_prof_array[1]));
                    //print_r($prof_information);
                    $term_information = $new_prof_array[4];
                    $term_array = explode(" ",$term_information);
            
            
                    //------- getting table 2 with information of class evaluations --------//
                    $table2 = $overall_array['eval'];
                    //display all H1 text
                    //print_r($array);
                    $rows = $table2->getElementsByTagName('tr');
                    $evaluation =
                                array(
                                      array(
                                            'question' => '', 'strongly disagree'=> '', 'disagree'=> '', 'neither d or a'=> '',
                                            'agree'=> '', 'strongly agree'=> '', 'median'=> '', 'tukey fence'=> '', '25%'=> '', '50%'=> '', '75%'=> ''
                                            )
                                      )
                                ;
                    $index = 0;
                    for ($i = 0; $i < $rows->length; $i++){
                        if($i <= 1){
                            continue;
                        }
                        $row = $rows->item($i);
                        $columns = $row->getElementsByTagName('td');
                        $col_array = array();
                        for ($s = 0; $s < $columns->length; $s++){
                            $col_array[] = $columns->item($s)->nodeValue;
                        }
                        //print_r($col_array);
                        $evaluation[$index]['question'] = $col_array[0];
                        $evaluation[$index]['strongly disagree'] = $col_array[1];
                        $evaluation[$index]['disagree'] = $col_array[2];
                        $evaluation[$index]['neither d or a'] = $col_array[3];
                        $evaluation[$index]['agree'] = $col_array[4];
                        $evaluation[$index]['strongly agree'] = $col_array[5];
                        $evaluation[$index]['median'] = $col_array[6];
                        $evaluation[$index]['tukey fence'] = $col_array[7];
                        $evaluation[$index]['25%'] = $col_array[8];
                        $evaluation[$index]['50%'] = $col_array[9];
                        $evaluation[$index]['75%'] = $col_array[10];
                        $index++;
                    }
            
                    //print_r($evaluation);
            
                    $parsed_array = array();
                    $parsed_array['user_first'] = $prof_information[0];
                    $parsed_array['user_last'] = $prof_information[1];
                    $parsed_array['course_name'] = $prof_information[2];
                    $parsed_array['course_number'] = $prof_information[3];
                    $parsed_array['course_component'] = $prof_information[4];
                    $parsed_array['course_section'] = $prof_information[5];
                    $parsed_array['month'] = $term_array[5];
                    $parsed_array['day'] = str_replace(",","",$term_array[6]);
                    $parsed_array['year'] = str_replace(".","",$term_array[7]);
                    $parsed_array['evaluation'] = $evaluation;
                    //print_r($parsed_array);
                $course_evaluations[] = $parsed_array;
             
            }
	    $this->evals = $course_evaluations;
	}

	function createEvalInfo($person, $evaluations_array){
            $courses = array();
            $userCourses = $person->getCourses();
            foreach($evaluations_array as $evaluations){
                $date_string = $evaluations['month']."-".$evaluations['day']."-".$evaluations['year'];
                $start_days = $this->getTermDays($this->parseDate($date_string));
                $course = Course::newFromSubjectCatalogSectStartDateTermLike($evaluations['course_name'], $evaluations['course_number'], '%', $start_days, '%');
                if($course->getId() == ""){
                /*                            $course = new Course(array());
                                            $course->subject = $evaluations['course_name'];
                                            $course->catalog = $evaluations['course_number'];
                                            $course->startDate = $start_days;
                                            $course->endDate = $start_days+97;
                                            $course->create();
                                            $course = Course::newFromSubjectCatalogSectStartDateTermLike($evaluations['course_name'], $evaluation['course_number'], '%', $start_days, '%');
                    */continue;
                }
                $found = false;
                foreach($userCourses as $userCourse){
                    if($evaluations['course_name'] == $userCourse->subject && $evaluations['course_number'] == $userCourse->catalog &&
                       $start_days == $userCourse->startDate){
                        $found = true;
                        break;
                    }
                }
                if(!$found){
                    $status = DBFunctions::insert('grand_user_courses',
                                                  array('course_id' => $course->getId(),
                                                        'user_id' => $person->getId()),
                                                  true);
                    if($status){
                        DBFunctions::commit();
                    }
                }
                $status = DBFunctions::update('grand_user_courses',
                                              array('course_evals' => serialize($evaluations)),
                                              array('course_id' => EQ($course->getId()),
                                                    'user_id' => EQ($person->getId())),
                                              array(),
                                              true);
                if($status){
                    DBFunctions::commit();
                    $courses[] = $status;
                }
            }
            return $courses;
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
	        $error = "";
            	$json = array('created' => array(),
                          'error' => array());
		$file_contents = file_get_contents($eval_file['tmp_name']);
		$this->setEvalInfo($file_contents);
		
		if(count($this->evals)>0){
		    $json['courses'] = $this->createEvalInfo($person, $this->evals);
		}
                $obj = json_encode($json);

		echo <<<EOF
            	<html>
                    <head>
                    	<script type='text/javascript'>
                                            parent.ccvUploaded($obj, "$error");
                    	</script>
                    </head>
            	</html>
EOF;
            	close();
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
	        close();
	    }
	}	
	
	function isLoginRequired(){
	    return true;
	}
    }
?>
