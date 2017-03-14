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
    
    function get_lines_between($string, $start, $end){
        $string = preg_replace('/<!--(.|\s)*?-->/', '', $string);
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
        
        $education = $this->get_lines_between($contents, "<b>Reason:</b>", "</div>");
        $index = 0;
        $key = -1;
        foreach($education as $line){
            if($line == ""){
                $index = count($data['Education']);
                $key = -1;
            }
            else{
                $key++;
            }
            switch($key){
                case 0:
                    $data['Education'][$index]['institution'] = $line;
                    break;
                case 1:
                    $data['Education'][$index]['country'] = $line;
                    break;
                case 2:
                    $data['Education'][$index]['degree'] = $line;
                    break;
                case 3:
                    $explode = explode(" ", $line);
                    $data['Education'][$index]['status'] = @$explode[0];
                    $data['Education'][$index]['start'] = @$explode[1];
                    $data['Education'][$index]['end'] = @$explode[2];
                    break;
                case 4:
                    $data['Education'][$index]['reason'] = $line;
                    break;
                    
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
        $userId = $_POST['id'];
        $tmpfile = $_FILES['file_field']['tmp_name'];
        $contents = file_get_contents($tmpfile);
        $data = $this->extract_pdf_data($contents);
        $sdata = serialize($data);
        $status = DBFunctions::update('grand_sop',
                                      array('pdf_data' => $sdata),
                                      array('user_id' => EQ($userId)));

        DBFunctions::commit();
                echo <<<EOF
                <html>
                    <head>
                        <script type='text/javascript'>
                            parent.ccvUploaded([], "Pdf Uploaded");
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
