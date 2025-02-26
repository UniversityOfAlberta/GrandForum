<?php

class UploadGrantXlsAPI extends API{
        
    var $grants = array();
    
    function processParams($params){
        //TODO
    }

    function formatDate($date){
        $array = explode("/", $date);
        return "{$array[2]}-{$array[0]}-{$array[1]} 00:00:00";
    }

    function setGrantInfo($xls_content, $xls2_content, $person){
        // xls_content = overview
        // xls2_content = details
        
        $grants = array();
        
        $headers  = array();
        $headers2 = array();
        foreach($xls_content as $rowN => $row){
            $row2 = $xls2_content[$rowN];
            if(implode("", $row) == "" && implode("", $row2) == ""){
                continue; // Empty Row
            }
            if(count($headers) == 0){
                foreach($row as $colN => $col){
                     $headers[] = $col;
                }
                foreach($row2 as $colN => $col){
                    $headers2[] = $col;
                }
            }
            else{
                $grant = new Grant(array());
                foreach($row as $colN => $col){
                    $head = $headers[$colN];
                    switch($head){
                        case "Project ID":
                            $grant->project_id = $col;
                            break;
                        case "Award End Date":
                            $grant->end_date = date('Y-m-d', strtotime($col));
                            break;
                        case "Total Award":
                            $grant->total = floatval(str_replace("$", "", str_replace(",", "", $col)));
                            break;
                        case "Funds Available Before Commitments":
                            $grant->funds_before = floatval(str_replace("$", "", str_replace(",", "", $col)));
                            break;
                        case "Funds Available After Commitments":
                            $grant->funds_after = floatval(str_replace("$", "", str_replace(",", "", $col)));
                            break;
                        case "Title":
                            $grant->title = $col;
                            break;
                        case "Description":
                            $grant->description = $col;
                            break;
                        case "Request":
                            $grant->request = $col;
                            break;
                    }
                }
                foreach($row2 as $colN => $col){
                    $head = $headers2[$colN];
                    switch($head){
                        case "Sponsor":
                            $grant->sponsor = $col;
                            break;
                        case "Award Begin Date":
                            $grant->start_date = date('Y-m-d', strtotime($col));
                            break;
                    }
                }
                if(!Grant::newFromProjectId($grant->getProjectId())->exists()){
                    $grant->user_id = $person->getId();
                    $grant->create();
                    $grants[] = $grant;
                }
            }
        }
        return $grants;
    }

    function checkXlsFile($xls){
        $data = explode("\n", file_get_contents($xls['tmp_name']));
        $flag = false;
        $grantArray1 = array();
        $linecount = 1;
        $array = array();
        foreach($data as $lines){
            if (preg_match('/Grants Life Cycle/', $lines) && $linecount >5){
                $flag = true;
                $count = 0;
                $row = array();
            }
            elseif($linecount == 5){
                preg_match_all('/\<th\>(.+?)\<\/th\>/',$lines, $array);
                unset($array[1][0]);
            }
            if($flag){
                $row[$array[1][$count+1]] = str_replace(array("<td>","</td>"), "",trim($lines));
                $count++;
                if($count > 10){
                    $flag = false;
                    $grantArray1[] = $row;
                }
            }   
            $linecount++;
        }
        if(isset($grantArray1[0]['Percent Spent'])){
            return true;
        }
        return false;
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
        global $wgMessage;
        $me = Person::newFromWgUser();
        if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
            $person = Person::newFromId($_POST['id']);
        }
        else{
            $person = $me;
        }
        $xls = $_FILES['grant'];
        $xls2 = $_FILES['grant2'];
        if((isset($xls['type']) && isset($xls2['type'])) &&
            ($xls['type'] == "application/vnd.ms-excel" || $xls['type'] == "application/octet-stream") &&
            ($xls2['type'] == "application/vnd.ms-excel" || $xls2['type'] == "application/octet-stream") &&
            $xls['size'] > 0 &&
            $xls2['size'] > 0){
            $error = "";
            $json = array('created' => array(),
                          'error' => array());
            if($this->checkXlsFile($xls)){
                $xls = $_FILES['grant2'];
                $xls2 = $_FILES['grant'];
            }
            
            $xls_cells  = $this->readXLS($xls['tmp_name']);
            $xls2_cells = $this->readXLS($xls2['tmp_name']);

            $grants = new Collection($this->setGrantInfo($xls_cells, $xls2_cells, $person));
            $json['created'] = $grants->toArray();
            $json = json_encode($json);
            
            echo <<<EOF
                <html>
                    <head>
                        <script type='text/javascript'>
                            parent.ccvUploaded($json, "$error");
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
