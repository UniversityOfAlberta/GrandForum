<?php

class UserGsmsFinalAPI extends API{

    function UserGsmsFinalAPI(){
    }

    function processParams($params){

    }

    function extract_excel_data($contents){
	$i = 0;
	$data_array = array();
	foreach($contents as $row){
	    if($i == 0){
		$i++;
		continue; //fix this to check instead of just skipping
	    }
	    elseif($row[2] == ""){
		break;
	    }
		//set student
	    $student_name = $row[2]." ".$row[1];
	    $array_info = array();
	    $array_info['name'] = $student_name;
	    $array_info['department'] = $row[0];
	    $array_info['gsms_id'] = $row[3];
	    $array_info['student_id'] = $row[4];
	    $array_info['cs_app'] = $row[5];
	    $array_info['dob'] = $row[6];
	    $array_info['email'] = $row[7];
	    $array_info['academic_year'] = $row[8];
	    $array_info['term'] = $row[9];
	    $array_info['program'] = $row[10];
	    $array_info['subplan_name'] = $row[11];
	    $array_info['degree'] = $row[12];
	    $array_info['program_name'] = $row[13];
            $array_info['admission_program_name'] = $row[14];
            $array_info['submitted_date'] = $row[15];
            $array_info['gender'] = $row[16];
            $array_info['country_of_birth'] = $row[17];
            $array_info['country_of_citizenship'] = $row[18];
            $array_info['application_type'] = $row[19];
            $array_info['folder'] = $row[20];
            $array_info['education_history'] = $row[21];
            $array_info['department_gpa'] = $row[22];
            $array_info['gpa_scale'] = $row[23];
            $array_info['normalized_gpa'] = $row[24];
            $array_info['fgsr_gpa'] = $row[25];
            $array_info['gpa_scale'] = $row[26];
            $array_info['normalized_gpa'] = $row[27];
            $array_info['elp_test'] = $row[28];
            $array_info['elp_score'] = $row[29];
            $array_info['listen'] = $row[30];
            $array_info['write'] = $row[31];
            $array_info['read'] = $row[32];
            $array_info['speaking'] = $row[33];
            $array_info['funding_note'] = $row[34];
            $array_info['department_decision'] = $row[35];
            $array_info['fgsr_decision'] = $row[36];
            $array_info['decision_response'] = $row[37];
            $array_info['general_notes'] = $row[38];
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
        $user = Person::newFromId($wgUser->getId());
        $xls = $_FILES['gsms_outcome'];
        if(isset($xls['type']) &&
            ($xls['type'] == "application/vnd.ms-excel" || $xls['type'] == "application/octet-stream") || $xls['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" &&
            $xls['size'] > 0 ){
            $error = "";
            $success = array();
            $errors = array();
            $xls_cells = $this->readXLS($xls['tmp_name']);
	    if($xls_cells === false){
		$errors[] = "Please upload a .xls file";
		$error_count++;
	    }
            $data = $this->extract_excel_data($xls_cells);
	    foreach($data as $student){
		$student_obj = Person::newFromNameLike($student['name']);	
		$student_id = $student_obj->getId();
		$student_name = $student['name'];
		  //check if student exists
		if($student_id != 0){
		   $error_count=0;
                   $update = false;
                  //check if update or new
                    $info_sheet = InfoSheet::newFromUserId($student_id);
                    if($info_sheet->user_id != ""){
                        $update = true;
                    }
                    if(!$update){
                    	$info_sheet->user_id = $student_id;
                        $info_sheet->create();
                    }
		    unset($student['name']);//added to make sure anonymous 
        	    DBFunctions::update('grand_person_gsms',
                            array('final_gsms' => serialize($student),
				  'final_decision' => $student['fgsr_decision']),
                            array('user_id' => EQ($student_id)));
		   //put update here
		    $success[] = $student_name;
                    DBFunctions::commit();
		}
		else{
			$errors[] = "<b>{$student['name']}</b> failed.  Student not found.";
		}
		
        }
	}
	else{
                $errors[] = "Please upload a .xls file";
                $error_count++;
	}
        //$success = (count($success) > 0) ? "<ul><li>".implode("</li><li>", $success)."</li></ul>" : "";
	if(count($errors) == 0){
		$success = "All students successfully updated.";
	}
	else{
		$success = (count($success) > 0) ? (count($success)) . " students were updated.";
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
