<?php

class PDFReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        $reportType = $this->getAttr("reportType", 'HQPReport');
        $useProject = $this->getAttr("project", false);
        $buttonName = $this->getAttr("buttonName", "Report PDF");
        $width = $this->getAttr("width", 'auto');
        $project = null;
        if($useProject){
            $project = Project::newFromId($this->projectId);
        }
        $person = Person::newFromId($this->personId);
        $year = (REPORTING_YEAR == date('Y'))? REPORTING_YEAR-1 : REPORTING_YEAR;
        $report = new DummyReport($reportType, $person, $project, $year);
        $tok = false;
        $tst = '';
        $len = 0;
        $sub = 0;
        $sto = new ReportStorage($person);
    	$check = $report->getPDF();
    	if (count($check) > 0) {
    		$tok = $check[0]['token'];
    		$sto->select_report($tok);    	
    		$tst = $sto->metadata('timestamp');
    		$len = $sto->metadata('len_pdf');
    		$sub = $sto->metadata('submitted');
    		$item = "<a class='button' style='width:{$width};' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>{$buttonName}</a>";
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
