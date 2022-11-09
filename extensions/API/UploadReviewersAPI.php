<?php

class UploadReviewersAPI extends API{

    function ConvertPdfAPI(){
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
		//set student
	    $student_array = explode(",", $row[0]);
	    $student_name = $student_array[1]." ".$student_array[0];
	    $reviewers = array();
		//set reviewer list
	    for($reviewer_count=1;$reviewer_count<=5;$reviewer_count++){
		if(isset($row[$reviewer_count]) && $row[$reviewer_count] != ""){
		    $reviewer_array = explode(",", $row[$reviewer_count]);
		    $reviewer_name = $reviewer_array[1]." ".$reviewer_array[0];
		    $reviewers[] = $reviewer_name;
		}
	    }
	    $data_array[$student_name] = $reviewers;
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
        $user = Person::newFromId($wgUser->getId());
        if(!$user->isRoleAtLeast(MANAGER)){
            return;
        }

        $xls = $_FILES['reviewers'];
        $file_year = YEAR;

        if(isset($xls['type']) &&
            ($xls['type'] == "application/vnd.ms-excel" || $xls['type'] == "application/octet-stream") &&
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
                                        array('year' => EQ($file_year)));
		if($status){
		    DBFunctions::commit();
		}
	    }
	    foreach($data as $student => $reviewers){
	        if(trim($student) == ""){
	            continue;
	        }
	        $gsmsMatches = array();
	        preg_match("/[0-9]+/", $student, $gsmsMatches);
	        $student = preg_replace("/[0-9]+/", "", $student);
	        $student_obj = null;
	        if(isset($gsmsMatches[0])){
	            $student_obj = Person::newFromGSMSId($gsmsMatches[0]);
	            if($student_obj != null){
		            $student_id = $student_obj->getId();
		        }
	        }
	        if($student_obj == null || $student_obj->getId() == 0){
	            $student_obj = Person::newFromNameLike($student);	
		        $student_id = $student_obj->getId();
	        }

		  //check if student exists
		if($student_id != 0){
			$error_count=0;
			 //check if reviewer exists
			foreach($reviewers as $reviewer){
			    if(trim($reviewer) == ""){
	                continue;
	            }
                $reviewer_obj = Person::newFromNameLike($reviewer);  
                $reviewer_id = $reviewer_obj->getId();
			    if($reviewer_id != 0){
        			$status = DBFunctions::insert('grand_eval',
                                		        array('user_id' => $reviewer_id,
							      'sub_id' => $student_id,
							      'type' => "sop",
							      'year' => $file_year),
                                   			       true);
				$sop_id = DBFunctions::select(array('grand_sop'),
                                             array('id'),
                                             array('user_id' => EQ($student_id)));

				if(count($sop_id) == 0){
	                                $status = DBFunctions::insert('grand_sop',
                                                        array('user_id' => $student_id),
                                                               true);

				}
//---
                    		$status = DBFunctions::update('grand_sop',
                                        array('reviewer' => "true"),
                                        array('user_id' => EQ($student_id)));

                    		if($status){
                           	    DBFunctions::commit();
                    		}
//--
			    }
			    else{
                    		$errors[] = "<b>$reviewer</b> failed. Reviewer not found.";
				$error_count++;
			    }
			    if($error_count ==0){
				$success[] = "<b>$student</b> successfully assigned to <b>{$reviewer_obj->getNameForForms()}</b>."; 
			    }
			}
		}
		else{
			$errors[] = "<b>$student</b> failed.  Student not found.";
		}
		
        }
	}
	else{
                $errors[] = "Please upload a .xls file";
                $error_count++;
	}
        $success = (count($success) > 0) ? "<ul><li>".implode("</li><li>", $success)."</li></ul>" : "";
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
