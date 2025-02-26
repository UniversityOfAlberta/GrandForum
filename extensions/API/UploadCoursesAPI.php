<?php
    class UploadCoursesAPI extends API{
	var $courses = array();
	function processParams($params){
	    //TODO
	}

       function getTermDays($date){
           $first_date = new DateTime("1900-01-01 00:00:00");
           $second_date = new DateTime($date);
           $difference = $first_date->diff($second_date)->format("%a");
           return $difference;
       }

        function getTermUsingStartMonth($month){
            if($month == "Sep"){
                return "Fall";
            }
            elseif($month == "Jan"){
                return "Winter";
            }
            elseif($month == "Apr"){
                return "Spring";
            }
            else{
                return "Summer";
            }
        }

	function setCourseInfo($xls_array){
	    $i = 0;
	    $finalarray = array();
            foreach ($xls_array as $Row){
		$checkRow = implode("",$Row);
		if($checkRow == ""){
		    continue;
		}
                if($i == 0){
                    $i++;
                    continue;
                }
                //Term	Comp. %
                $class_array = explode(" ",$Row[3]);
                //subject
                $class = $class_array[0];
                $number_array = explode("-",$class_array[1]);
                //number
                $number = $number_array[0];
                //section
                $section = $number_array[1];
                //enrolled
                $enrolled = $Row[5];
                //component
                $title_array = explode(" ",$Row[4]);
                $comp = str_replace("(","",str_replace(")","",array_pop($title_array)));
                //start_date
                $term_array = explode(" ",$Row[9]);
                $start_date = $term_array[0];
                //end date
                $end_date = $term_array[1];
                //term
                $start_date_array = explode("-",$start_date);
                $month_date = $start_date_array[1];
                $term = $this->getTermUsingStartMonth($month_date);
		//room 
		$room = $Row[7];
		//days
		$days = $Row[6];
                $i++;
		$finalarray[] = array(
					'subject'=>$class,
					'number' => $number,
					'section' => $section,
					'enrolled' => $enrolled,
					'component' => $comp,
					'start_date' =>substr($start_date, 0, -1),
					'end_date'=>$end_date,
					'term' =>$term,
					'room' =>$room,
					'days' =>$days
					);
            }

	    $this->courses = $finalarray;
	}

	function createCourseInfo($person, $courses){
	    $success = array();
	    foreach($courses as $course){
		$start_days = $this->getTermDays($course['start_date']);
		$end_days = $this->getTermDays($course['end_date']);
		$new_course = Course::newFromSubjectCatalogSectStartDateTermLike($course['subject'],$course['number'],$course['section'],$start_days);
		if($new_course->getId() == ""){
		    $new_course = new Course(array());
                    $new_course->subject = $course['subject'];
                    $new_course->catalog = $course['number'];
                    $new_course->startDate = $start_days;
                    $new_course->endDate = $end_days;
		    $new_course->shortDesc = $term;
		    $new_course->component = $course['component'];
		    $new_course->sect = $course['section'];
		    $new_course->totEnrl = $course['enrolled'];
		    $new_course->place = $course['room'];
		    $new_course->note = $course['days'];
                    $new_course->create();
                    $new_course = Course::newFromSubjectCatalogSectStartDateTermLike($course['subject'],$course['number'],$course['section'],$start_days);
		}
                    $status = DBFunctions::insert('grand_user_courses',
                                                 array('course_id' => $new_course->getId(),
                                                      'user_id' => $person->getId()),
                                                true);
		    if($status){
                        DBFunctions::commit();
                        $success[] = $status;
                     }
            }
	    return $success;
	}

	function doAction($noEcho=false){
	    global $wgMessage;
            $dir = dirname(__FILE__);
            require_once($dir . '/../../Classes/PHPExcel/IOFactory.php');

	    $me = Person::newFromWgUser();
	    if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
	   	$person = Person::newFromId($_POST['id']);
	    }
	    else{
		$person = $me;
	    }
	    $xls = $_FILES['courses'];
	    $objReader = PHPExcel_IOFactory::createReaderForFile($xls['tmp_name']);
	    $objReader->setReadDataOnly(true);
	    $obj = $objReader->load($xls['tmp_name']);
	    $obj->setActiveSheetIndex(0);
	    $xls_array = $obj->getActiveSheet()->toArray();
	    if(isset($xls['type'])
	        && ($xls['type'] == "application/vnd.ms-excel" 
		    || $xls['type'] == "application/octet-stream" 
		    || $xls['type'] == "application/octet-streamArray")
	        && $xls['size'] > 0){
                $error = "";
                $json = array('created' => array(),
                          'error' => array());
		//call parser that will create array
		$this->setCourseInfo($xls_array);
		if(count($this->courses)>0){
		    $funding = $this->courses;
			//go through array and save all into database
		    $json['funding'] = $this->createCourseInfo($person, $this->courses);
		    $json['fundingFail'] = count($funding) - count($json['funding']);
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
