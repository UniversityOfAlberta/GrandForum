<?php

class UploadReportItem extends AbstractReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		if(isset($_GET['downloadFile'])){
		    
		    $data = unserialize($this->getBlobValue());
		    if($data != null){
		        header("Content-disposition: attachment; filename='{$data['name']}'");
		        echo base64_decode($data['file']);
		        exit;
		    }
		}
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
		
		$html .= "<div id='budgetDiv'><iframe id='budgetFrame0' frameborder='0' style='border-width:0;height:100px;width:100%;' scrolling='none' src='../index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm{$projectGet}{$year}'></iframe></div>";
		$html .= "</div>";
		
		$item = $this->processCData($html);
		$wgOut->addHTML("$item");
	}
	
	function renderForPDF(){
        // DO NOTHING
	}
	
	function fileUploadForm(){
	    global $wgServer, $wgScriptPath;
	    if(isset($_POST['upload'])){
	        $this->save();
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
        
        echo "<html>
                <head>
                    <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.min.js'></script>
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/basetemplate.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/template.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/main.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/cavendish.css' type='text/css' />
                    <script type='text/javascript'>
                        function load_page() {
                            parent.alertsize($(\"body\").height()+38);
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
        if(isset($_POST['upload'])){
            echo "<script type='text/javascript'>
                        parent.alertreload();
                    </script>";
        }
        echo "</head>
              <body style='margin:0;'>
                    <div id='bodyContent'>
                        <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&fileUploadForm{$projectGet}{$year}' method='post' enctype='multipart/form-data'>                      <p>
                                <b>Max File Size:</b> {$this->getAttr('fileSize', 10)} MB
                            </p>
                            <input type='file' name='file' />
	                        <input type='submit' name='upload' value='Upload' />
	                    </form>";
	    $data = $this->getBlobValue();
	    if($data !== null){
	        echo "<br /><a href='$wgServer$wgScriptPath/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."{$projectGet}{$year}&downloadFile'>Download Uploaded File</a>";
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
	    if(isset($_FILES['file']) && $_FILES['file']['tmp_name'] != ""){
	        if($this->getAttr('fileSize', 10)*1024*1024 <= $_FILES['file']['size']){
	            $name = $_FILES['file']['name'];
	            $type = $_FILES['file']['type'];
	            $size = $_FILES['file']['size'];
	            $contents = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
	            $hash = md5($contents);
	            $data = array('name' => $name,
	                          'type' => $type,
	                          'size' => $size,
	                          'hash' => $hash,
	                          'file' => $contents);
	            $this->setBlobValue(serialize($data));
	        }
	        else{
	            echo "The uploaded file is larger than the allowed size.";
	        }
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
