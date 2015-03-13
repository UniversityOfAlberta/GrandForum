<?php

class UploadReportItem extends AbstractReportItem {

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        if(isset($_GET['fileUploadForm'])){
            $this->fileUploadForm();
        }
        $projectGet = "";
        if(isset($_GET['project'])){
            $projectGet = "&project={$_GET['project']}";
        }
        $year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        
        $report = $this->getReport();
        $section = $this->getSection();
        
        $html = "<script type='text/javascript'>
                                var frameId = 0;
                                function alertreload(){
                                    var lastHeight = $('#budgetFrame' + frameId).height();
                                    $('#budgetFrame' + frameId).remove();
                                    frameId++;
                                    $('#budgetDiv').html(\"<iframe id='fileFrame\" + frameId + \"' style='border-width:0;width:100%;' frameborder='0' src='../index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm{$projectGet}{$year}'></iframe>\");
                                    $('#budgetFrame' + frameId).height(lastHeight);
                                }
                                function alertsize(pixels){
                                    $('#reportMain > div').stop();
                                    $('#budgetFrame' + frameId).height(pixels);
                                    $('#budgetFrame' + frameId).css('max-height', pixels);
                                }
                            </script>";
        $html .= "<div>";
        
        $html .= "<div id='budgetDiv'><iframe id='budgetFrame0' frameborder='0' style='border-width:0;height:65px;width:100%;' scrolling='none' src='../index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm{$projectGet}{$year}'></iframe></div>";
        $html .= "</div>";
        
        $item = $this->processCData($html);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $data = $this->getBlobValue();
        $link = $this->getDownloadLink();
        $html = ($data !== null && $data !== "") ? "<a class='externalLink' href='{$link}'>Download</a>" : "";
        $item = $this->processCData($html);
        $wgOut->addHTML($item);
    }
    
    function fileUploadForm(){
        global $wgServer, $wgScriptPath;
        $projectGet = "";
        if(isset($_GET['project'])){
            $projectGet = "&project={$_GET['project']}";
        }
        $year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        
        $report = $this->getReport();
        $section = $this->getSection();
        
        echo "<html>
                <head>
                    <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.min.js'></script>
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/basetemplate.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/template.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/main.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/cavendish.css' type='text/css' />
                    <script type='text/javascript'>
                        function load_page() {
                            parent.alertsize($(\"body > div\").height() + 10);
                        }
                    </script>
                    <style type='text/css'>
                        body {
                            background: none;
                            padding-bottom:25px;
                            overflow-y: hidden;
                        }
                        
                        #bodyContent {
                            font-size: 9pt;
                            font-family: Verdana, sans-serif;
                            -moz-border-radius: 0px;
                            -webkit-border-radius: 0px;
                            border-radius: 0px;
                            
                            -webkit-box-shadow: none;
                            -moz-box-shadow: none;
                            box-shadow: none;
                            border-width:0;
                            padding:0;
                        }
                        
                        table {
                            line-height: 1.5em;
                            font-size: 9pt;
                            font-family: Verdana, sans-serif;
                        }
                    </style>";
        echo "</head>
              <body style='margin:0;'>
                    <div>";
        if(isset($_POST['upload'])){
            $this->save();
        }
        echo "          <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm{$projectGet}{$year}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='file' />
                            <input type='submit' name='upload' value='Upload' /> <b>Max File Size:</b> {$this->getAttr('fileSize', 1)} MB
                        </form>";
        $data = $this->getBlobValue();
        if($data !== null && $data !== ""){
            echo "<br /><a href='{$this->getDownloadLink()}'>Download Uploaded File</a>";
        }
        else{
            echo "<div>You have not uploaded a file yet</div>";
        }
        echo "      </div>
                </body>
                <script type='text/javascript'>
                    $(document).ready(function(){
                        load_page();
                        setTimeout(load_page, 200);
                    });
                </script>
              </html>";
        exit;
    }
    
    function save(){
        global $wgFileExtensions;
        if(isset($_FILES['file']) && $_FILES['file']['tmp_name'] != ""){
            $name = $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            list($partname, $ext) = UploadForm::splitExtensions($name);
            if(count($ext)){
                $finalExt = $ext[count($ext) - 1];
            }
            else{
                $finalExt = '';
            }
            if($this->getAttr('fileSize', 1)*1024*1024 >= $_FILES['file']['size']){
                $magic = MimeMagic::singleton();
                $mime = $magic->guessMimeType($_FILES['file']['tmp_name'], false);
                if(UploadForm::checkFileExtension($finalExt, $wgFileExtensions) &&
                   UploadForm::verifyExtension($mime, $finalExt)){
                    $contents = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
                    $hash = md5($contents);
                    $data = array('name' => $name,
                                  'type' => $mime,
                                  'size' => $size,
                                  'hash' => $hash,
                                  'file' => $contents);
                    $this->setBlobValue(json_encode($data));
                    echo "<div class='success'>The file was uploaded successfully.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }
                else if(!UploadForm::checkFileExtension($finalExt, $wgFileExtensions)){
                    echo "<div class='error'>Uploads of the type <i>.{$finalExt}</i> are not allowed.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }
                else if(!UploadForm::verifyExtension($mime, $finalExt)){
                    echo "<div class='error'>The uploaded file extension does not match its type, or it is corrupt.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }
            }
            else{
                echo "<div class='error'>The uploaded file is larger than the allowed size of ".($this->getAttr('fileSize', 1))."MB.</div>";
                unset($_POST['upload']);
                $this->fileUploadForm();
                exit;
            }
        }
        if(isset($_POST['upload'])){
            unset($_POST['upload']);
            $this->fileUploadForm();
        }
        return array();
    }
    
    function getNFields(){
        return 0;
    }
    
    function getNComplete(){
        return 0;
    }
}

?>
