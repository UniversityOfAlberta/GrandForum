<?php

class ReportSubmissionStatusItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
        $details = $this->getHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getHTML(){
	    $reportType = $this->getAttr('reportType', 'HQPReport');
        $person = Person::newFromId($this->personId);
        $useProject = $this->getAttr("project", "true");
        $project = null;
        if($useProject == "true"){
            $project = Project::newFromHistoricId($this->projectId);
        }
        $report = new DummyReport($reportType, $person, $project);
        $check = $report->getPDF();
        if(count($check) > 0){
            return $check[0]['status'];
        }
        return "Not Generated/Not Submitted";
	}
}

?>
