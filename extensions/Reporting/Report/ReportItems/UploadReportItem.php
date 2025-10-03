<?php

class UploadReportItem extends AbstractReportItem {

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        if(isset($_GET['delete']) && 
           $_GET['delete'] != "" &&
            (($this->getMD5(false) == $_GET['delete']) ||
             (decrypt($this->getMD5(false), true) != "" && decrypt($this->getMD5(false), true) == decrypt($_GET['delete'], true)))){
            $this->delete();
            close();
        }
        
        if(strtolower($this->getAttr("pdf")) == "true"){
            $this->renderForPDF();
            return;
        }
        
        if(isset($_GET['fileUploadForm']) && $_GET['fileUploadForm'] == $this->getPostId()){
            $this->fileUploadForm();
        }
        $projectGet = "";
        if(isset($_GET['project'])){
            $projectGet = "&project={$_GET['project']}";
        }
        $personId = (isset($_GET['person'])) ? "&person=".urlencode($_GET['person']) : "";
        $year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        
        $candidate = (isset($_GET['candidate'])) ? "&candidate=".urlencode($_GET['candidate']) : "";
        $id = (isset($_GET['id'])) ? "&id=".urlencode($_GET['id']) : "";
        
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
        
        $html .= "<div id='budgetDiv'><iframe id='fileFrame{$this->getPostId()}' class='uploadFrame' frameborder='0' style='border-width:0;height:65px;width:100%;min-height:65px;' scrolling='none' src='../index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->getPostId())."&fileUploadForm={$this->getPostId()}{$projectGet}{$personId}{$year}{$candidate}{$id}'></iframe></div>";
        $html .= "</div>";
        
        $item = $this->processCData($html);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut, $wgServer, $wgScriptPath;
        $data = $this->getBlobValue();
        $link = $this->getDownloadLink();
        $html = "";
        if($data !== null && $data != ""){
            $json = json_decode($data);
            $name = $json->name;
            $deleteHTML = "";
            if(!isset($_GET['generatePDF'])){
                $projectGet = "";
                if(isset($_GET['project'])){
                    $projectGet = "&project={$_GET['project']}";
                }
                $year = "";
                if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
                    $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
                }
                $personId = (isset($_GET['person'])) ? "&person=".urlencode($_GET['person']) : "";
                
                $report = $this->getReport();
                $section = $this->getSection();
                $deleteHTML = "&nbsp;<button id='delete{$this->getPostId()}' type='button' class='button'>Delete</button>";
                $deleteHTML .= "<script type='text/javascript'>
                    $('#delete{$this->getPostId()}').click(function(){
                        if(confirm('Are you sure you want to delete this upload?')){
                            $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->getPostId())."&delete={$this->getMD5()}{$projectGet}{$personId}{$year}', function(){
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
        global $wgServer, $wgScriptPath, $wgLang;
        $me = Person::newFromWgUser();
        $projectGet = "";
        if(isset($_GET['project'])){
            $projectGet = "&project={$_GET['project']}";
        }
        $personId = (isset($_GET['person'])) ? "&person=".urlencode($_GET['person']) : "";
        $year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        
        $candidate = (isset($_GET['candidate'])) ? "&candidate=".urlencode($_GET['candidate']) : "";
        $id = (isset($_GET['id'])) ? "&id=".urlencode($_GET['id']) : "";
        
        $report = $this->getReport();
        $section = $this->getSection();
        $width = $this->getAttr("width", "100%");
        echo "<html>
                <head>
                    <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.min.js'></script>
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/basetemplate.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/template.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/main.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/cavendish.css' type='text/css' />
                    <script type='text/javascript'>
                        function load_page(){
                            var interval = setInterval(function(){
                                if($(\"body > div\").is(':visible')){
                                    parent.alertsize_{$this->getPostId()}($(\"body > div\").height() + 10);
                                    clearInterval(interval);
                                }
                            }, 200);
                        }
                    </script>
                    <style type='text/css'>
                        body {
                            background: none;
                            padding-bottom:25px;
                            overflow-y: hidden;
                            overflow-x: hidden;
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
                        }";
                        
                        if($wgLang->getCode() == "en"){
		                    echo "fr, .fr { display: none !important; }";
		                }
		                else if($wgLang->getCode() == "fr"){
		                    echo "en, .en { display: none !important; }";
		                }
                        
                        echo "
                        table {
                            line-height: 1.5em;
                            font-size: 9pt;
                            font-family: Verdana, sans-serif;
                        }
                    </style>";
        echo "</head>
              <body style='margin: 0; width: {$width};'>
                    <div style='width: {$width};'>";
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
        $fileSizeMessage = ($this->getAttr("showMaxFileSize", "true") === "true") ? "<span class='en'>Max File Size</span><span class='fr'>Taille maximale du fichier</span>: {$this->getAttr('fileSize', 1)} MB" : "";

        echo "          <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->getPostId())."&fileUploadForm={$this->getPostId()}{$projectGet}{$personId}{$year}{$candidate}{$id}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='file' accept='{$this->getAttr('mimeType')}' />
                            <button type='submit' name='upload' value='Upload'><span class='en'>Upload</span><span class='fr'>Télécharger</span></button> {$fileSizeMessage}<br />
                            <small><i><b><span class='en'>NOTE</span><span class='fr'>NB</span>:</b> <span class='en'>Uploading a new file replaces the old one</span><span class='fr'>Téléchargé un nouveau fichier remplace l’ancien</span></i></small>
                        </form>";
        $data = $this->getBlobValue();
        if($data !== null && $data !== ""){
            $json = json_decode($data);
            $name = $json->name;
            $downloadText = ($me->isLoggedIn()) ? "<a href='{$this->getDownloadLink()}'><en>Download</en><fr>Télécharger</fr> <b>{$name}</b></a>&nbsp;" : "<b>File Uploaded</b>&nbsp;";
            echo "<br />{$downloadText}
                        <button id='delete' type='button' class='button'><en>Delete</en><fr>Supprimer</fr></button>";
        }
        else{
            if($this->getAttr('mimeType') == "application/pdf"){
                echo "<div>You have not uploaded a PDF file yet</div>";
            }
            else{
                echo "<div>You have not uploaded a file yet</div>";
            }
        }
        echo "      </div>
                </body>
                <script type='text/javascript'>
                    $(document).ready(function(){
                        load_page();
                        parent.uploadFramesSaving['fileFrame{$this->getPostId()}'] = false;
                    });
                    
                    $('#delete').click(function(){
                        if(confirm('Are you sure you want to delete this upload?')){
                            $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->getPostId())."&delete={$this->getMD5()}{$projectGet}{$year}{$candidate}{$id}', function(){
                                parent.updateProgress();
                                window.location = window.location;
                            });
                        };
                    });
                    
                </script>
                
              </html>";
        close();
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
                $magic = MediaWiki\MediaWikiServices::getInstance()->getMimeAnalyzer();
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
                    echo "<script type='text/javascript'>
                        parent.updateProgress();
                    </script>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    close();
                }
                else if(!UploadBase::checkFileExtension($finalExt, $wgFileExtensions)){
                    echo "<div class='error'>Uploads of the type <i>.{$finalExt}</i> are not allowed.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    close();
                }
                /*else if(!UploadBase::verifyExtension($mime, $finalExt)){
                    echo "<div class='error'>The uploaded file extension does not match its type, or it is corrupt.</div>";
                    unset($_POST['upload']);
                    $this->fileUploadForm();
                    close();
                }*/
            }
            else{
                echo "<div class='error'>The uploaded file is larger than the allowed size of ".($this->getAttr('fileSize', 1))."MB.</div>";
                unset($_POST['upload']);
                $this->fileUploadForm();
                close();
            }
        }
        if(isset($_POST['upload'])){
            unset($_POST['upload']);
            $this->fileUploadForm();
        }
        return array();
    }
}

?>
