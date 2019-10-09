<?php

class UserOTBioAPI extends API{

    function UserOTBioAPI(){
    }

    function processParams($params){

    }

    function extract_excel_data($contents){
	$i = 0;
	$data_array = array();
	foreach($contents as $row){
	    if($i == 0){
		$i++;
		continue;
	    }
		//set student information
	    $array_info = array();
	    $array_info['lastname'] = trim($row[0]);
            $array_info['firstname'] = trim($row[1]);
            $array_info['email'] = trim($row[2]);
            $array_info['gsms_id'] = trim($row[3]);
            $array_info['student_id'] = trim($row[4]);
            $array_info['user_id'] = trim($row[5]);
            $array_info['country'] = trim($row[6]);

              //degrees
            /*$degrees = array();
            $degree_array = explode(",",trim($row[7]));
            if(count($degree_array) >0){
                foreach($degree_array as $degree){
                    $degree = trim($degree);
                    $degree = trim(preg_replace('/\s+/', ' ', $degree));
                    if($degree == ""){
                        continue;
                    }
                    $new_degree = array();
                    $pattern = '/(.*)\((.*)\)/';
                    preg_match($pattern, $degree, $matches);
                    if($matches[1] != "" && $matches[2] != ""){
                        $new_degree['degree'] = trim($matches[1]);
                        $new_degree['institution'] = trim($matches[2]);
                        $degrees[] = $new_degree;
                    }
                }
            }*/
            $array_info['degrees'] = array();
            $array_info['degree_text'] = trim($row[7]);

	     //setting nationality notes (must fix this in future)
            $nationality_notes = trim($row[8]);

            $array_info['indigenous'] = "";
            $array_info['canadian'] = "";
            $array_info['saskatchewan'] = "";
            $array_info['international'] = "";
            if(strpos($nationality_notes, 'Indigenous') !== false){
                $array_info['indigenous'] = "Yes";
            }
            if(strpos($nationality_notes, 'Canadian') !== false){
                $array_info['canadian'] = "Yes";
            }
            if(strpos($nationality_notes, 'Saskatchewan') !== false){
                $array_info['saskatchewan'] = "Yes";
            }
            if(strpos($nationality_notes, 'International') !== false){
                $array_info['international'] = "Yes";
            }

            $array_info['gpa60'] = $row[9];

             //Best GPA / number of credits
                $array_info['gpafull'] = "";
                $array_info['gpafull_credits'] = "";
                $array_info['gpafull2'] = "";
                $array_info['gpafull_credits2'] = "";

            $gpa_array = explode("/",trim($row[10]));
            if(count($gpa_array) >0 && $gpa_array[0] != ""){
                $array_info['gpafull'] = trim($gpa_array[0]);
                $array_info['gpafull_credits'] = trim($gpa_array[1]);
            }
            $gpa_array = explode("/",trim($row[11]));

            if(count($gpa_array) >0 && $gpa_array[0] != ""){
                $array_info['gpafull2'] = trim($gpa_array[0]);
                $array_info['gpafull_credits2'] = trim($gpa_array[1]);
            }
            $array_info['anatomy'] = trim($row[12]);
            $array_info['stats'] = trim($row[13]);
            $casper = trim($row[14]);
            $array_info['casper'] = number_format($casper, 2);
            $reviewers = array();
            $reviewer_array = explode(",", trim($row[15]));
            if(count($reviewer_array) >0){
                foreach($reviewer_array as $reviewer){
                    $reviewer = trim($reviewer);
                    $reviewer = trim(preg_replace('/\s+/', ' ', $reviewer));
                    if($reviewer == ""){
                        continue;
                    }
                    elseif(strpos($reviewer, "(") !== false){
                        $pattern = '/(.*)\((.*)\)/';
                        preg_match($pattern, $reviewer, $matches);
                        $reviewers[] = trim($matches[1]);
                    }
                    else{
                        $reviewers[] = $reviewer;
                    }
                }
            }
            $array_info['reviewers'] = $reviewers;

            $array_info['withdrawals'] = 0;
            $array_info['failures'] = 0;
            $array_info['notes'] = trim($row[17]);



	    $data_array[] = $array_info;
	}
	return $data_array;
    }

    function readXLS($file){
        $dir = dirname(__FILE__);
        require_once($dir . '/../../Classes/PHPExcel/IOFactory.php');

        $objReader = PHPExcel_IOFactory::createReaderForFile($file);
        $class = get_class($objReader);
        if($class != "PHPExcel_Reader_Excel5" && $class != "PHPExcel_Reader_Excel2007" && $class != "PHPExcel_Reader_HTML"){
            return false;
        }
        $objReader->setReadDataOnly(true);
        $obj = $objReader->load($file);
        $obj->setActiveSheetIndex(0);
        $cells = $obj->getActiveSheet()->toArray();
        return $cells;
    }

    function doAction($noEcho=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang;
         //check if at least manager
        $user = Person::newFromId($wgUser->getId());
        if(!$user->isRoleAtLeast(MANAGER)){
            return;
        }
         //get file and make sure correct format
        $xls = $_FILES['students_gsms'];
        if(isset($xls['type']) &&
            ($xls['type'] == "application/vnd.ms-excel" || $xls['type'] == "application/octet-stream" || $xls['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
             || $xls['type'] == 'text/csv')&&
            $xls['size'] > 0 ){
            $error = "";
            $xls_cells = $this->readXLS($xls['tmp_name']);
	    if($xls_cells === false){
		$errors[] = "Please upload a .xls file";
		$error_count++;
	    }
            $success = array();
            $errors = array();
            $data = $this->extract_excel_data($xls_cells);
            if(count($data)>0){
                $status = DBFunctions::delete('grand_eval',
                                        array('year' => YEAR));
                if($status){
                    DBFunctions::commit();
                }
            }
	    foreach($data as $student){
                  //get student
		$student_obj = Person::newFromId($student['user_id']);
		$student_id = $student_obj->getId();
                $student_obj->firstName = $student['firstname'];
                $student_obj->lastName = $student['lastname'];
                $student_obj->realname = "{$student['firstname']} {$student['lastname']}";
                $student_obj->update();
		  //check to make sure student exists
		if($student_id != 0){
		   $error_count = 0;
		   $update = false;
		  //check if update or new
                   $data = DBFunctions::select(array('grand_gsms'),
                                               array('user_id'),
                                               array('user_id'=> EQ($student_id)));
                   if(count($data)==0){
                       DBFunctions::insert('grand_gsms',
                                     array('user_id' => $student_id),
                                           true);
                   }
                   $gsms = GsmsData::newFromUserId($student_id);
                   foreach($student as $field => $value){
                        $gsms->setAdditional($field, $add);
                   }
                   $gsms->student_id = $student['student_id'];
                   $gsms->update();
                   
                   $gsmsId = $gsms->getId();
                   Cache::delete("gsms_{$gsmsId}");
                   Cache::delete("gsms_user_{$student_id}");
                    /*$reviewers = $student['reviewers'];
                    foreach($reviewers as $reviewer){
                        $reviewer_obj = Person::newFromNameLike($reviewer);
                        $reviewer_id = $reviewer_obj->getId();
                        if($reviewer_id != 0){
                            $status = DBFunctions::insert('grand_eval',
                                                   array('user_id' => $reviewer_id,
                                                         'sub_id' => $student_id,
                                                         'type' => "sop", 
                                                         'year' => YEAR),
                                                          true);
                        }
                        else{
                            $errors[] = "<b>$reviewer</b> assignment failed. Reviewer not found or duplicated.";
                            $error_count++;
                        }
                    }*/
                    DBFunctions::commit();
		    $success[] = "<b>{$student['lastname']},{$student['firstname']}</b> updated.";
		}
		else{
			$errors[] = "<b>{$student['lastname']},{$student['firstname']}</b> failed.  Student not found.";
		}
		
        }
	}
	else{
                $errors[] = "Please upload a .xls file";
                $error_count++;
	}
	if(count($success) <= 6){
            $success = (count($success) > 0) ? "<ul><li>".implode("</li><li>", $success)."</li></ul>" : "";
	}
	else{
	    $success = "All updates successful.";
	}
        $errors = (count($errors) > 0) ? "<ul><li>".implode("</li><li>", $errors)."</li></ul>" : "";


        DBFunctions::commit();
                echo <<<EOF
                <html>
                    <head>
                        <script type='text/javascript'>
                            parent.ccvUploaded("$success", "$errors");
                        </script>
                    </head>
                </html>
EOF;
        exit;
    }

   function isLoginRequired(){
       return true;
   }
}
?>
