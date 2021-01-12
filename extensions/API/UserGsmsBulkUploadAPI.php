<?php

class UserGsmsBulkUploadAPI extends API{

    function UserGsmsBulkUploadAPI(){


    }

    function processParams($params){

    }

    function getCurrentStudentsDifference(){


    }

    function extract_excel_data($contents){
        $i = 0;
        $data_array = array();
        foreach($contents as $row){
            if($i == 0){
                $i++;
                continue; //fix this to check instead of just skipping
            }
            else if($row[1] == "" && $row[2] == ""){
                continue;
            }
            $application_year_array = explode("/",$row[8]);
            $application_year = $application_year_array[0]; 
            if($application_year != YEAR+1){
                continue;
            }
            $in_data_array = false;
            $student_name = $row[2]." ".$row[1];
            $gsms_id = $row[3];
            foreach($data_array as $student){
                if($student['gsms_id'] == $gsms_id){
                    $program_name = $data_array[$gsms_id]['program_name'];
                    if($program_name != "{$row['13']}"){
                        $row[13] .= ", {$program_name}";
                    }
                }
            }
            foreach($row as $k => $cell){
                $row[$k] = trim($cell);
            }
            //set student
            $array_info = array();
            if($row[11] == "Multimedia"){
                // Ignore Multimedia rows
                continue;
            }
            if(isset($data_array[$gsms_id]) &&
               (strstr($data_array[$gsms_id]['folder'], "Evaluator") !== false ||
                strstr($data_array[$gsms_id]['folder'], "Coder") !== false ||
                strstr($data_array[$gsms_id]['folder'], "Offer Accepted") !== false ||
                strstr($data_array[$gsms_id]['folder'], "Waiting for Response") !== false ||
                strstr($data_array[$gsms_id]['folder'], "Incoming") !== false ||
                strstr($data_array[$gsms_id]['folder'], "Admit") !== false)){
                $row[14] = $data_array[$gsms_id]['admission_program_name'];
                $row[20] = $data_array[$gsms_id]['folder'];
            }
            
            $offset = 0;
            
            $array_info['name'] = $student_name;
            $array_info['department'] = $row[0+$offset];
            $array_info['gsms_id'] = $row[3+$offset];
            $array_info['student_id'] = "{$row[4+$offset]}";
            $array_info['cs_app'] = $row[5+$offset];
            $array_info['date_of_birth'] = $row[6+$offset]." 00:00:00";
            $array_info['email'] = $row[7+$offset];
            $array_info['academic_year'] = $row[8+$offset];
            $array_info['term'] = $row[9+$offset];
            $array_info['program'] = $row[10+$offset];
            $array_info['subplan_name'] = $row[11+$offset];
            $array_info['degree'] = $row[12+$offset];
            $array_info['program_name'] = $row[13+$offset];
            $array_info['admission_program_name'] = $row[14+$offset];
            $array_info['submitted_date'] = $row[15+$offset];
            $array_info['gender'] = $row[16+$offset];
            $array_info['country_of_birth'] = $row[17+$offset];
            $array_info['country_of_citizenship'] = $row[18+$offset];
            $array_info['applicant_type'] = $row[19+$offset];
            $array_info['folder'] = $row[20+$offset];
            $array_info['education_history'] = $row[21+$offset];
            if(trim($array_info['education_history']) == ""){
                $offset--;
            }
            $array_info['department_gpa'] = $row[22+$offset];
            $array_info['gpa_scale'] = $row[23+$offset];
            $array_info['normalized_gpa'] = $row[24+$offset];
            $array_info['fgsr_gpa'] = $row[25+$offset];
            $array_info['gpa_scale'] = $row[26+$offset];
            $array_info['normalized_gpa'] = $row[27+$offset];
            // Extra columns?
            $array_info['epl_test'] = $row[30+$offset];
            $array_info['epl_score'] = $row[31+$offset];
            $array_info['epl_listen'] = $row[32+$offset];
            $array_info['epl_write'] = $row[33+$offset];
            $array_info['epl_read'] = $row[34+$offset];
            $array_info['epl_speaking'] = $row[35+$offset];
            $array_info['funding_note'] = $row[36+$offset];
            $array_info['department_decision'] = $row[37+$offset];
            $array_info['fgsr_decision'] = $row[38+$offset];
            $array_info['decision_response'] = $row[39+$offset];
            $array_info['general_notes'] = $row[40+$offset];
            $data_array[$gsms_id] = $array_info;
        }
        return $data_array;
    }

    function readXLS($file){
        $dir = dirname(__FILE__);
        require_once($dir . '/../../Classes/PHPExcel/IOFactory.php');
        $objReader = PHPExcel_IOFactory::createReaderForFile($file);
        $class = get_class($objReader);
        if($class != "PHPExcel_Reader_Excel5" && $class != "PHPExcel_Reader_Excel2007" && $class != "PHPExcel_Reader_HTML" && $class != "PHPExcel_Reader_CSV"){
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
        $notfound = array();
        $found_gsms = array();
        $updated_students = array();
        if(!$user->isRoleAtLeast(MANAGER)){
            return;
        }
        ini_set('max_execution_time', 300);
        $xls = $_FILES['gsms_outcome'];
        if(isset($xls['type']) &&
            ($xls['type'] == "application/vnd.ms-excel" || $xls['type'] == "application/octet-stream" || $xls['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" 
             || $xls['type'] == 'text/csv')&&
            $xls['size'] > 0 ){
            $error = "";
            $errors = array();
            $xls_cells = $this->readXLS($xls['tmp_name']);
            if($xls_cells === false){
                $errors[] = "Please upload a .xls or .csv file";
            }
            $data = $this->extract_excel_data($xls_cells);
            foreach($data as $student){
                $student_obj = Person::newFromGSMSId($student['gsms_id']);
                if($student_obj == null || $student_obj->getId() == 0){
                    // GSMS ID not found, try email
                    $student_obj = Person::newFromEmail($student['email']);
                }
                if($student_obj == null || $student_obj->getId() == 0){
                    // GSMS ID and Email not found, skip this student, show error
                    $errors[] = "<b>{$student['name']}</b> failed.  Student not found.";
                    $notfound[] = "{$student['gsms_id']},{$student['name']},{$student['email']},{$student['folder']}";
                    continue;
                }
                $student_id = $student_obj->getId();
                $student_name = $student['name'];
                //check if student exists
                if($student_id != 0){
                    //check to make sure submitted gsms
                    $gsms_sheet = GsmsData::newFromUserId($student_id);
                    if($gsms_sheet->user_id == ""){
                        $notfound[] = "{$student['gsms_id']},{$student['name']},{$student['email']},{$student['folder']}";
                        continue;
                    }
                    $found_gsms[] = "'{$student['gsms_id']}'";
                    $alreadyProcessed = (isset($updated_students[$gsms_sheet->user_id]));

                    $updated_students[$gsms_sheet->user_id] = "{$student['name']} ({$student['email']})";
                    $update = false;
                    //check if update or new
                    $gsms_sheet = GsmsData::newFromUserId($student_id);
                    $gsms_sheet->gender = @$student['gender'];
                    $gsms_sheet->gsms_id = @$student['gsms_id'];
                    $gsms_sheet->student_id = @"{$student['student_id']}";
                    $gsms_sheet->date_of_birth = @$student['date_of_birth'];
                    $gsms_sheet->program_name = @$student['program_name'];
                    $gsms_sheet->country_of_birth = @$student['country_of_birth'];
                    $gsms_sheet->country_of_citizenship = @$student['country_of_citizenship'];
                    $gsms_sheet->applicant_type = @$student['applicant_type'];
                    $gsms_sheet->education_history = @$student['education_history'];
                    $gsms_sheet->department = @$student['department'];
                    if($alreadyProcessed && $gsms_sheet->epl_test != "" && $gsms_sheet->epl_test != @$student['epl_test']){
                        $gsms_sheet->epl_test .= @",{$student['epl_test']}";
                        $gsms_sheet->epl_score .= @",{$student['epl_score']}";
                        $gsms_sheet->epl_listen .= @",{$student['epl_listen']}";
                        $gsms_sheet->epl_write .= @",{$student['epl_write']}";
                        $gsms_sheet->epl_read .= @",{$student['epl_read']}";
                        $gsms_sheet->epl_speaking .= @",{$student['epl_speaking']}";
                    }
                    else{
                        $gsms_sheet->epl_test = @$student['epl_test'];
                        $gsms_sheet->epl_score = @$student['epl_score'];
                        $gsms_sheet->epl_listen = @$student['epl_listen'];
                        $gsms_sheet->epl_write = @$student['epl_write'];
                        $gsms_sheet->epl_read = @$student['epl_read'];
                        $gsms_sheet->epl_speaking = @$student['epl_speaking'];
                    }
                    /*$gsms_sheet->epl_test = @$student['epl_test'];
                    $gsms_sheet->epl_score = @$student['epl_score'];
                    $gsms_sheet->epl_listen = @$student['epl_listen'];
                    $gsms_sheet->epl_write = @$student['epl_write'];
                    $gsms_sheet->epl_read = @$student['epl_read'];
                    $gsms_sheet->epl_speaking = @$student['epl_speaking'];*/
                    $gsms_sheet->cs_app = @$student['cs_app'];
                    $gsms_sheet->academic_year = @$student['academic_year'];
                    $gsms_sheet->term = @$student['term'];
                    $gsms_sheet->subplan_name = @$student['subplan_name'];
                    $gsms_sheet->program = @$student['program'];
                    $gsms_sheet->degree_code = @$student['degree_code'];
                    $gsms_sheet->admission_program_name = @$student['admission_program_name'];
                    $gsms_sheet->submitted_date = @$student['submitted_date'];
                    $gsms_sheet->folder = @$student['folder'];
                    $gsms_sheet->department_gpa = @$student['department_gpa'];
                    $gsms_sheet->department_gpa_scale = @$student['department_gpa_scale'];
                    $gsms_sheet->department_normalized_gpa = @$student['department_normalized_gpa'];
                    $gsms_sheet->fgsr_gpa = @$student['fgsr_gpa'];
                    $gsms_sheet->fgsr_gpa_scale = @$student['fgsr_gpa_scale'];
                    $gsms_sheet->fgsr_normalized_gpa = @$student['fgsr_normalized_gpa'];
                    $gsms_sheet->funding_note = @$student['funding_note'];
                    $gsms_sheet->department_decision = @$student['department_decision'];
                    $gsms_sheet->fgsr_decision = @$student['fgsr_decision'];
                    $gsms_sheet->decision_response = @$student['decision_response'];
                    $gsms_sheet->general_notes = @$student['general_notes'];
                    $gsms_sheet->visible = 'true';
                    $gsms_sheet->update();
                }
                else{
                    $notfound[] = "{$student['gsms_id']},{$student['name']},{$student['email']},{$student['folder']}";
                    $error_count= $error_count+1;
                }
            }
        }
        else{
            $errors[] = "Please upload a .xls or .csv file";
        }
        //successfully updated students:
        $updated_students_string = implode("<br />", $updated_students);
        $success = "<b>The following students were updated properly</b>:<br />".$updated_students_string;
        
        //students not found in gsms table:
        $not_in_gars = array();
        $not_finished = array();
        foreach($notfound as $student_gsms_string){
            $student_gsms_array = explode(",",$student_gsms_string);
            $student_gsms = $student_gsms_array[0];
            $student_obj = Person::newFromGSMSId($student_gsms);
            if($student_obj == null){
                $not_in_gars[] = "{$student_gsms_array[1]}({$student_gsms_array[2]}) - {$student_gsms_array[3]}";
            }
            else{
                $not_finished[] = "{$student_obj->getRealName()}({$student_obj->getEmail()}) - {$student_gsms_array[3]}";
            }
        }
        $not_in_gars_string = implode("<br />", $not_in_gars);
        $not_finished_string = implode("<br />", $not_finished);
        $in_gars_string = "";
        //students found in gsms table but not in csv:
        /*$foundgsmsstring = implode(", ", $found_gsms);
        $in_gars = array();
        $sql = "SELECT DISTINCT(user_id) FROM grand_gsms WHERE gsms_id NOT IN ($foundgsmsstring)";
        $data = DBFunctions::execSQL($sql);
        if(count($data)>0){
            foreach($data as $student_id){
                $student = Person::newFromId($student_id['user_id']);
                $real_name = $student->getRealName();
                $email = $student->getEmail();
                $in_gars[] = "$real_name ($email)";
            }
        }
        $in_gars_string = implode("<br />", $in_gars);*/
        //putting everything together
        $errors = "<b>The following students from GSMS do not have a GARS account:</b><br />$not_in_gars_string<br /><br /><b>The following students have a GARS account, but have not submitted an application yet:</b><br />$not_finished_string<br /><br /><b>The following students have a GARS application, but are not in GSMS:</b><br />$in_gars_string";

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
