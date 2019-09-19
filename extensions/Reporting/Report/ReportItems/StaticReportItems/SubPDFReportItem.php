<?php

class SubPDFReportItem extends StaticReportItem {

    function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        $reportType = $this->getAttr("reportType", 'HQPReport');
        
        $width = $this->getAttr("width", 'auto');
        
        //echo $this->personId ."<br>";
        $project = null;
        $person = Person::newFromId($this->personId);
        $person_name = $person->getReversedName();
        $report = new DummyReport($reportType, $person, $project);
        $tok = false;
        $tst = '';
        $len = 0;
        $sub = 0;
        $sto = new ReportStorage($person, $project);
        $check = $report->getPDF();
        if (count($check) > 0) {
            $tok = $check[0]['token'];
            $sto->select_report($tok);        
            $tst = $sto->metadata('timestamp');
            $len = $sto->metadata('len_pdf');
            $sub = $sto->metadata('submitted');

            $item = "<td>{$person_name}</td><td><a class='button' style='width:{$width};' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>Report PDF</a></td>";
            
            $item = $this->processCData($item);
            
            $wgOut->addHTML($item);
        }
        else{
            $wgOut->addHTML($this->processCData(""));
        }
        
    }
    
    function renderForPDF(){
        global $wgOut;
        $wgOut->addHTML($this->processCData(""));
    }
}

?>
