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
		//set student
	    $student_array = explode(",", $row[0]);
	    $student_name = $student_array[1]." ".$student_array[0];
	    $array_info = array();
	    $array_info['name'] = $student_name;
	    $array_info['gpa60'] = $row[1];
	    $gpa_array = explode("/",$row[2]);
	    $array_info['gpafull'] = $gpa_array[0];
	    $array_info['gpafull_credits'] = $gpa_array[1];
            $gpa_array = explode("/",$row[3]);
            $array_info['gpafull2'] = $gpa_array[0];
            $array_info['gpafull_credits2'] = $gpa_array[1];
	    $array_info['failures'] = $row[4];
	    $array_info['withdrawals'] = $row[5];
	    $array_info['notes'] = $row[6];
	    $array_info['indigenous'] = $row[7];
	    $array_info['canadian'] = $row[8];
	    $array_info['saskatchewan'] = $row[9];
	    $array_info['international'] = $row[10];
	    $array_info['anatomy'] = $row[11];
	    $array_info['stats'] = $row[12];
	    $degrees = array();
	    $degree_array = explode(",",$row[13]);
	    foreach($degree_array as $degree){
		$new_degree = array();
		$pattern = '/(.*)\((.*)\)/';
		preg_match($pattern, $degree, $matches);
		$new_degree['degree'] = $matches[1];
		$new_degree['institution'] = $matches[2];
		$degrees[] = $new_degree;
	    }
	    $array_info['degrees'] = $degrees;
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
        if(!$user->isRoleAtLeast(MANAGER)){
            return;
        }
        $xls = $_FILES['students_gsms'];
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
	    foreach($data as $student){
		$student_obj = Person::newFromNameLike($student['name']);	
		$student_id = $student_obj->getId();
		  //check if student exists
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
                   
                   DBFunctions::update('grand_gsms',
                            array('additional' => serialize($student)),
                            array('user_id' => EQ($student_id)));
                    DBFunctions::commit();
		    $success[] = "<b>{$student['name']}</b> updated.";
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
