<?php

class ConvertPdfAPI extends API{

    function ConvertPdfAPI(){
    }

    function processParams($params){

    }

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        preg_match(str_replace(" ", ".", "/$start/s"), $string, $matches, PREG_OFFSET_CAPTURE);
        $ini = @$matches[0][1];
        if ($ini == 0) return '';
        $ini += strlen($start);
        preg_match(str_replace(" ", ".", "/$end/s"), $string, $matches, PREG_OFFSET_CAPTURE);
        $ini2 = @$matches[0][1];
        $len = $ini2 - $ini;
        $substr = substr($string, $ini, $len);
        return trim($substr);
    }
    
    function get_education($string){
        $start = "<b>Reason:</b>";
        $end = "Other&#160;Comments";
        $string = preg_replace('/<!--[^>]*-->/', '', $string);
        $lines = explode("\n", $string);
        $startFound = false;
        $between = array();
        $educations = array();
        
        $lastTop = 0;
        $id = -1;
        foreach($lines as $line){
            if($startFound && strstr($line, $end) !== false){
                break;
            }
            if($startFound){
                if(strstr($line, "PDF Created by University of Alberta") !== false ||
                   strstr($line, "Page ") !== false ||
                   strstr($line, "Application&#160;Submitted") !== false ||
                   strstr($line, "PDF&#160;Created") !== false){
                    continue;
                }
                if(strstr($line, "<p") !== false){
                    $top = explode("top:", $line);
                    $top = @explode(";", $top[1]);
                    $top = $top[0];
                    if($top != $lastTop){
                        $id++;
                    }
                    $educations[$id][] = trim(strip_tags(str_replace("<br/>", " ", str_replace("&#160;", " ", $line))));
                    $lastTop = $top;
                }
            }
            else if(strstr($line, $start) !== false){
                $startFound = true;
            }
        }
        return $educations;
    }
    
    function get_lines_between($string, $start, $end){
        $string = preg_replace('/<!--[^>]*-->/', '', $string);
        $lines = explode("\n", $string);
        $startFound = false;
        $between = array();
        
        foreach($lines as $line){
            if($startFound && strstr($line, $end) !== false){
                break;
            }
            if($startFound){
                $between[] = trim(strip_tags(str_replace("<br/>", " ", str_replace("&#160;", " ", $line))));
            }
            else if(strstr($line, $start) !== false){
                $startFound = true;
            }
        }
        return $between;
    }

    function extract_pdf_data($contents){
        $fileName = md5($contents + rand());
        file_put_contents("/tmp/{$fileName}", $contents);
        exec("pdftohtml -noframes -s -i /tmp/{$fileName} /tmp/{$fileName}.html 2>&1");
        $contents = file_get_contents("/tmp/{$fileName}.html");
        unlink("/tmp/{$fileName}");
        unlink("/tmp/{$fileName}.html");
        
        $data = array();
        
        $nameData = $this->get_lines_between($contents, "Applicant&#160;Details", "Applicant&#160;ID");
        
        foreach($nameData as $line){
            $exploded = explode(":", $line);
            if($exploded[0] == "Surname"){
                $data['last_name'] = ucwords(strtolower(trim($exploded[1])));
            }
            else if($exploded[0] == "First and Middle Name(s)"){
                $data['first_name'] = ucwords(strtolower(trim($exploded[1])));
            }
        }
        
        $education = $this->get_education($contents);
        $key = -1;
        foreach($education as $index => $line){
            $data['Education'][$index] = $line;
            continue;
            foreach($line as $key => $cell){
                switch($key){
                    case 0:
                        $data['Education'][$index]['institution'] = $cell;
                        break;
                    case 1:
                        $data['Education'][$index]['country'] = $cell;
                        break;
                    case 2:
                        $data['Education'][$index]['degree'] = $cell;
                        break;
                    case 3:
                        $explode = explode(" ", $cell);
                        $data['Education'][$index]['status'] = @$explode[0];
                        $data['Education'][$index]['start'] = @$explode[1];
                        $data['Education'][$index]['end'] = @$explode[2];
                        break;
                    case 4:
                        $data['Education'][$index]['reason'] = $cell;
                        break;
                }
            }
        }
        $referees = array();
        $referees[] = $this->get_lines_between($contents, "Referee&#160;1&#160;Information", "Referee&#160;2&#160;Information");
        $referees[] = $this->get_lines_between($contents, "Referee&#160;2&#160;Information", "<b>Documents</b>");
        
        foreach($referees as $refId => $referee){
            foreach($referee as $key => $line){
                switch($key){
                    case 0:
                        $explode = explode(":", $line);
                        $data['Referees'][$refId]['name'] = @$explode[1];
                        break;
                    case 1:
                        $explode = explode(":", $line);
                        $data['Referees'][$refId]['type_of_reference'] = @$explode[1];
                        break;
                    case 2:
                        $explode = explode(":", $line);
                        $data['Referees'][$refId]['email'] = @$explode[1];
                        break;
                    case 3:
                        $explode = explode(":", $line);
                        $data['Referees'][$refId]['phone'] = @$explode[1];
                        break;
                    case 4:
                        $explode = explode(":", $line);
                        $data['Referees'][$refId]['institution'] = @$explode[1];
                        break;
                    case 5:
                        $explode = explode(":", $line);
                        $data['Referees'][$refId]['position'] = @$explode[1];
                        break;
                    case 6:
                        break;
                    default:
                        if($line != ""){
                            @$data['Referees'][$refId]['responses'] .= str_replace("'","",$line)."\n";
                        }
                        break;
                }
            }
        }
        return $data;
    }

    function doAction($noEcho=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang;
        $user = Person::newFromId($wgUser->getId());
        if(!$user->isRoleAtLeast(MANAGER)){
            return;
        }   
        
        $tmpfiles = $_FILES['file_field']['tmp_name'];
        if(!is_array($tmpfiles)){
            $tmpfiles = array($tmpfiles);
        }
        
        $success = array();
        $errors = array();
        $num_file = 0;
        foreach($tmpfiles as $tmpfile){
            exec("extensions/Reporting/PDFGenerator/gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dCompatibilityLevel=1.4 -sOutputFile=\"{$tmpfile}.out\" \"{$tmpfile}\" &> /dev/null", $output, $ret);
            if($ret === 0){
                // Ghostscript conversion worked
                $contents = file_get_contents("{$tmpfile}.out");
                unlink("{$tmpfile}.out");
            }
            else{
                // Ghostscript conversion failed
                $contents = file_get_contents("{$tmpfile}");
            }
            $data = $this->extract_pdf_data($contents);
            if(isset($_POST['id'])){
                $userId = $_POST['id'];
            }
            else{
                $files_array = explode(".",$_FILES['file_field']['name'][$num_file]);
                $person = Person::newFromGSMSId($files_array[0]);
                if($person == null){
                    $errors[] = "<b>{$data['first_name']} {$data['last_name']} ({$files_array[0]})</b> failed.  User not found.";
                    $num_file++;
                    continue;
                }
                $userId = $person->getId();
            }
            $num_file++;
            if($userId != 0){
                $content_parsed = DBFunctions::escape(gzdeflate($contents));
                unset($contents);
                // Person Found
                $person = Person::newFromId($userId);
                $sdata = serialize($data);
                unset($data);
		        if(count(DBFunctions::select(array('grand_sop'),
		                                     array('user_id'),
		                                     array('user_id' => EQ($userId)))) > 0){
		            $success[] = "<b>{$person->getNameForForms()}</b> uploaded";
                    DBFunctions::update('grand_sop',
                                        array('pdf_data' => $sdata),
                                        array('user_id' => EQ($userId)));
            
                    $sql = "update grand_sop 
	                    set pdf_contents = '$content_parsed'
	                    where user_id = '$userId'";
	                unset($content_parsed);
                    $data = DBFunctions::execSQL($sql, true);
                    if($data){
	                    DBFunctions::commit();
                    }
                }
                else{
                    // SOP Not Found
                    $errors[] = "<b>{$person->getNameForForms()}</b> failed. User exists, but application not submitted.  Might be a duplicate accout.";
                }
            }
            else{
                // Person not found
                $errors[] = "<b>{$data['first_name']} {$data['last_name']}</b> failed.  User not found.";
            }
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
