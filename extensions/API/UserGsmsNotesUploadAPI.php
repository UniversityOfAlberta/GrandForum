<?php

class UserGsmsNotesUploadAPI extends API{

    function UserGsmsNotesUploadAPI(){


    }

    function processParams($params){

    }

    function extract_excel_data($contents){
        $data_array = array();
        foreach($contents as $row){
            $data_array[] = array('gsms_id' => $row[0],
                                  'notes' => "{$row[1]}");
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
        $updated_students = array();
        if(!$user->isRoleAtLeast(MANAGER)){
            return;
        }
        ini_set('max_execution_time', 300);
        $xls = $_FILES['gsms_notes'];
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
                    // GSMS ID and Email not found, skip this student, show error
                    $errors[] = "<b>{$student['gsms_id']}</b> failed.  Student not found.";
                    $notfound[] = "{$student['gsms_id']}";
                    continue;
                }
                $student_id = $student_obj->getId();
                //check if student exists
                if($student_id != 0){
                    //check to make sure submitted gsms
                    $gsms_sheet = GsmsData::newFromUserId($student_id);
                    if($gsms_sheet->user_id == ""){
                        $notfound[] = "{$student['gsms_id']}";
                        continue;
                    }

                    $updated_students[$gsms_sheet->user_id] = "{$student_obj->getNameForForms()} ({$student['gsms_id']})";
                    //check if update or new
                    $gsms_sheet = GsmsData::newFromUserId($student_id);
                    @$gsms_sheet->additional['notes']->{$user->getLastName()} = $student['notes'];
                    $gsms_sheet->update();
                }
                else{
                    $notfound[] = "{$student['gsms_id']}";
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
            $not_in_gars[] = "{$student_gsms_string}";
        }
        $not_in_gars_string = implode("<br />", $not_in_gars);
        $not_finished_string = implode("<br />", $not_finished);
        $in_gars_string = "";
        
        //putting everything together
        $errors = "<b>The following students from GSMS do not have a GARS account:</b><br />$not_in_gars_string";

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
