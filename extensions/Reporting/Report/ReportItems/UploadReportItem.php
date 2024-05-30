<?php

class UploadReportItem extends AbstractReportItem {

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        if(isset($_GET['delete']) && 
           $_GET['delete'] != "" &&
            (($this->getMD5(false) == $_GET['delete']) ||
             (decrypt($this->getMD5(false), true) != "" && decrypt($this->getMD5(false), true) == decrypt($_GET['delete'], true)))){
            $this->delete();
            exit;
        }
        
        if(isset($_GET['fileUploadForm']) && $_GET['fileUploadForm'] == $this->getPostId()){
            $this->fileUploadForm();
        }
        $projectGet = "";
        $userGet = "";
        if(isset($_GET['project'])){
            $projectGet = "&project={$_GET['project']}";
        }
        if(isset($_GET['sop_id'])){
            $projectGet = "&sop_id={$_GET['sop_id']}";
        }
        if(isset($_GET['userId'])){
            $userGet = "&userId={$_GET['userId']}";
        }
        
        $report = $this->getReport();
        $section = $this->getSection();
        
        $html = "<script type='text/javascript'>
                                function alertsize_{$this->getPostId()}(pixels){
                                    $('#reportMain > div').stop();
                                    $('#fileFrame{$this->getPostId()}').height(pixels);
                                    $('#fileFrame{$this->getPostId()}').css('max-height', pixels);
                                }
                            </script>";
        $html .= "<div>";
        
        $html .= "<div id='budgetDiv'><iframe id='fileFrame{$this->getPostId()}' class='uploadFrame' frameborder='0' style='border-width:0;height:65px;width:100%;min-height:65px;' scrolling='none' src='../index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm={$this->getPostId()}{$projectGet}{$userGet}'></iframe></div>";
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
            $deleteHTML = "";
            if(!isset($_GET['generatePDF'])){
                $projectGet = "";
                $userGet = "";
                if(isset($_GET['project'])){
                    $projectGet = "&project={$_GET['project']}";
                }
                if(isset($_GET['userId'])){
                    $userGet = "&userId={$_GET['userId']}";
                }
                $year = "";
                if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
                    $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
                }
                
                $report = $this->getReport();
                $section = $this->getSection();
                $deleteHTML = "&nbsp;<button id='delete{$this->getPostId()}' type='button' class='button'>Delete</button>";
                $deleteHTML .= "<script type='text/javascript'>
                    $('#delete{$this->getPostId()}').click(function(){
                        if(confirm('Are you sure you want to delete this upload?')){
                            $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&delete={$this->getMD5()}{$projectGet}{$userId}{$year}', function(){
                                $('#upload{$this->getPostId()}').hide();
                            });
                        };
                    });
                </script>";
                
            }
            $html = "<p id='upload{$this->getPostId()}'><a class='externalLink' href='{$link}'>Download&nbsp;<b>{$name}</b></a>{$deleteHTML}</p>";
            if(isset($_GET['generatePDF'])){
                $html .= "<br />";
            }
        }
        $item = $this->processCData($html);
        $wgOut->addHTML($item);
    }
    
    function fileUploadForm(){
        global $wgServer, $wgScriptPath;
        $projectGet = "";
        $userGet = "";
        if(isset($_GET['project'])){
            $projectGet = "&project={$_GET['project']}";
        }
        if(isset($_GET['sop_id'])){
            $projectGet = "&sop_id={$_GET['sop_id']}";
        }
        if(isset($_GET['userId'])){
            $userGet = "&userId={$_GET['userId']}";
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
                            parent.alertsize_{$this->getPostId()}($(\"body > div\").height() + 10);
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
        if(!$this->getSection()->checkPermission('w')){
            echo "<script type='text/javascript'>
                $(document).ready(function(){
                    $('textarea').prop('disabled', 'disabled');
                    $('input').prop('disabled', 'disabled');
                    $('button').prop('disabled', 'disabled');
                });
            </script>";
        }
        echo "          <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm={$this->getPostId()}{$projectGet}{$userGet}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='file' accept='{$this->getAttr('mimeType')}' />
                            <input type='submit' name='upload' value='Upload' /> <b>Max File Size:</b> {$this->getAttr('fileSize', 1)} MB
                        </form>";
        $data = $this->getBlobValue();
        if($data !== null && $data !== ""){
            $json = json_decode($data);
            $name = $json->name;
            echo "<br /><a href='{$this->getDownloadLink()}'>Download <b>{$name}</b></a>&nbsp;
                        <button id='delete' type='button' class='button'>Delete</button>";
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
                        parent.uploadFramesSaving['fileFrame{$this->getPostId()}'] = false;
                    });
                    
                    $('#delete').click(function(){
                        if(confirm('Are you sure you want to delete this upload?')){
                            $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&delete={$this->getMD5()}{$projectGet}{$userGet}', function(){
                                parent.updateProgress();
                                window.location = window.location;
                            });
                        };
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
                if(UploadBase::checkFileExtension($finalExt, $wgFileExtensions) &&
                   UploadBase::verifyExtension($mime, $finalExt)){
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
                else if(!UploadBase::checkFileExtension($finalExt, $wgFileExtensions)){
                    echo "<div class='error'>Uploads of the type <i>.{$finalExt}</i> are not allowed.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    exit;
                }
                else if(!UploadBase::verifyExtension($mime, $finalExt)){
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
