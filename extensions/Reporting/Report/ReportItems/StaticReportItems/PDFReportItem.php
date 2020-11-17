<?php

class PDFReportItem extends StaticReportItem {

    function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = $this->getReport()->person;
        $reportType = $this->getAttr("reportType", 'HQPReport');
        $useProject = $this->getAttr("project", false);
        $class = $this->getAttr("class", "button");
        $showIfNull = $this->getAttr("showIfNull", "false");
        $buttonName = $this->getAttr("buttonName", "Report PDF");
        $noRenderIfNull = $this->getAttr("noRenderIfNull", "false");
        $year = $this->getAttr("year", $this->getReport()->year);
        $section = $this->getAttr("section", "");
        $width = $this->getAttr("width", 'auto');
        $height = $this->getAttr("height", "700px");
        $useHTML = (strtolower($this->getAttr("useHTML", "false")) == "true");
        $preview = (strtolower($this->getAttr("preview", "false")) == "true");
        $embed = (strtolower($this->getAttr("embed", "false")) == "true");
        if(strstr($width, "%") !== false){
            $width = $width.";-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box";
        }
        $project = null;
        if($useProject){
            $project = Project::newFromId($this->projectId);
        }
        $person = Person::newFromId($this->personId);
        $report = new DummyReport($reportType, $person, $project, $year);
        $report->person = $person;
        
        if($preview && !isset($_GET['generatePDF'])){
            if(count($report->pdfFiles) > 0){
                foreach($report->pdfFiles as $pdfFile){
                    $report = new DummyReport($pdfFile, $person, $project, $year);
                    $report->person = $person;
                    $wgOut->addHTML("<div style='max-height:{$height};overflow-y:auto;padding:30px;border:15px solid #AAAAAA;'>");
                    $report->renderForPDF();
                    $wgOut->addHTML("</div>");
                }
            }
            return;
        }
        
        $tok = false;
        $check = $report->getPDF(false, $section);
        if (count($check) > 0) {
            $tok = $check[0]['token'];
            if($embed){
                $item = "<iframe src='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}' width='100%' height='{$height}' frameborder='0'></iframe>";
            }
            else if($useHTML){
                $item = "<iframe src='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&html' width='100%' height='{$height}' frameborder='0'></iframe>";
            }
            else {
                $item = "<a class='$class' style='width:{$width};' target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>{$buttonName}</a>";
            }
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
        else{
            if($noRenderIfNull == "true"){
                return;
            }
            else if($showIfNull == "true"){
                $wgOut->addHTML($this->processCData("{$buttonName}"));
            }
            else{
                $item = "<a class='$class' style='width:{$width};display:none;' target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>{$buttonName}</a>";
                $wgOut->addHTML($this->processCData($item));
            }
        }
    }

    function renderForPDF(){
        global $wgOut;
        $wgOut->addHTML($this->processCData(""));
    }
}

?>
