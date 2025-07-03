<?php

class GenericBudgetReportItem extends AbstractReportItem {

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        if(isset($_GET['downloadBudget'])){
            $data = $this->getBlobValue();
            if($data != null){
                $person = Person::newFromId($wgUser->getId());
                header('Content-Type: application/vnd.ms-excel');
                header("Content-disposition: attachment; filename='{$person->getNameForForms()}_Budget.xls'");
                echo $data;
                exit;
            }
        }
        if(isset($_GET['budgetUploadForm'])){
            $this->budgetUploadForm();
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
        $report = $this->getReport()->xmlName;
        $section = str_replace(" ", "", $this->getSection()->name);
        $title = $this->getAttr("title", "Budget Upload");
        $template = $this->getAttr("template", "");
        $wgOut->addHTML("<script type='text/javascript'>
                                var frameId = 0;
                                function alertreload(){
                                    $('#$section').click();
                                }
                            </script>");
        $wgOut->addHTML("<div>");
        $wgOut->addHTML("<h2>{$title}</h2>
                         <p><b>Template:</b> <a href='$wgServer$wgScriptPath/data/{$template}'>{$template}</a></p>
                         <div id='budgetDiv'><iframe name='budget' id='budgetFrame0' frameborder='0' style='border-width:0;height:60px;width:100%;' scrolling='none' src='../index.php/Special:Report?report=$report&section=$section&budgetUploadForm{$projectGet}{$personId}{$year}'></iframe></div>");
        $wgOut->addHTML("</div>");
    }
    
    function renderForPDF(){
        // Do Nothing
    }
    
    function budgetUploadForm(){
        global $wgServer, $wgScriptPath;
        if(isset($_POST['upload'])){
            $this->save();
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
        $report = $this->getReport()->xmlName;
        $section = $this->getSection()->name;
        echo "<html>
                <head>
                    <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.min.js'></script>
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/basetemplate.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/template.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/main.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/cavendish.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/highlights.css.php' type='text/css' />

                    <style type='text/css'>
                        body {
                            background: none;
                            padding-bottom:25px;
                            overflow-y: hidden;
                        }
                        
                        #bodyContent {
                            position: relative;
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
                            top: 0;
                            left: 0;
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
                        <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$report}&section={$section}&budgetUploadForm{$projectGet}{$personId}{$year}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='budget' />
                            <input type='submit' name='upload' value='Upload' />
                        </form>";
        $data = $this->getBlobValue();
	    if($data !== null){
	        echo "<br /><a href='$wgServer$wgScriptPath/index.php/Special:Report?report=NIReport&section=Budget&downloadBudget{$projectGet}{$personId}{$year}'>Download Uploaded Budget</a>";
	    }
        echo "      </div>
                </body>
              </html>";
        exit;
    }
    
    function save(){
        if(isset($_FILES['budget']) && $_FILES['budget']['tmp_name'] != ""){
            $contents = utf8_encode(file_get_contents($_FILES['budget']['tmp_name']));
            $this->setBlobValue($contents);
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
