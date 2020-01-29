<?php

class UploadReportItem extends AbstractReportItem {

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        if(isset($_GET['fileUploadForm']) && $_GET['fileUploadForm'] == $this->getPostId()){
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

        $html = "<div>";
        
        $html .= "<div id='budgetDiv'><iframe id='fileFrame{$this->getPostId()}' class='uploadFrame' frameborder='0' style='border-width:0;height:88px;width:100%;min-height:65px;' scrolling='none' src='../index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm={$this->getPostId()}{$projectGet}{$year}'></iframe></div>";
        $html .= "</div>";
        
        $item = $this->processCData($html);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $data = $this->getBlobValue();
        $link = $this->getDownloadLink();
        $html = "";
        if($data !== null && $data != ""){
            $json = json_decode($data);
            $name = $json->name;
            $html = "<p><a class='externalLink' href='{$link}'>Download&nbsp;<b>{$name}</b></a></p><br />";
        }
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
        if(!$this->getSection()->checkPermission('w')){
            echo "<script type='text/javascript'>
                $(document).ready(function(){
                    $('textarea').prop('disabled', 'disabled');
                    $('input').prop('disabled', 'disabled');
                    $('button').prop('disabled', 'disabled');
                });
            </script>";
        }
        echo "          <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm={$this->getPostId()}{$projectGet}{$year}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='file' accept='{$this->getAttr('mimeType')}' />
                            <input type='submit' name='upload' value='Upload' /> <b>Max File Size:</b> {$this->getAttr('fileSize', 1)} MB<br />
                            <small><i><b>NOTE:</b> Uploading a new file replaces the old one</i></small>
                        </form>";
        $data = $this->getBlobValue();
        if($data !== null && $data !== ""){
            $json = json_decode($data);
            $name = $json->name;
            echo "<br /><a href='{$this->getDownloadLink()}'>Download <b>{$name}</b></a>";
        }
        else{
            echo "<div>You have not uploaded a file yet</div>";
        }
        echo "      </div>
                </body>
                <script type='text/javascript'>
                    $(document).ready(function(){
                        parent.uploadFramesSaving['fileFrame{$this->getPostId()}'] = false;
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
            list($partname, $ext) = UploadBase::splitExtensions($name);
            if(count($ext)){
                $finalExt = $ext[count($ext) - 1];
            }
            else{
                $finalExt = '';
            }
            if($this->getAttr('fileSize', 1)*1024*1024 >= $_FILES['file']['size']){
                $magic = MimeMagic::singleton();
                $mime = $magic->guessMimeType($_FILES['file']['tmp_name'], false);
                if(UploadBase::checkFileExtension($finalExt, $wgFileExtensions)){
                    $contents = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
                    $hash = md5($contents);
                    $data = array('name' => $name,
                                  'type' => $mime,
                                  'size' => $size,
                                  'hash' => $hash,
                                  'file' => $contents);
                    $json = json_encode($data);
                    unset($contents);
                    unset($data);
                    $this->setBlobValue($json);
                    echo "<div class='success'>The file was uploaded successfully.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }
                else if(!UploadBase::checkFileExtension($finalExt, $wgFileExtensions)){
                    echo "<div class='error'>Uploads of the type <i>.{$finalExt}</i> are not allowed.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }
                /*else if(!UploadBase::verifyExtension($mime, $finalExt)){
                    echo "<div class='error'>The uploaded file extension does not match its type, or it is corrupt.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }*/
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
