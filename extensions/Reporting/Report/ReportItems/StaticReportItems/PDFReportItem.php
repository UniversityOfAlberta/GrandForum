<?php

class PDFReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = $this->getReport()->person;
        $reportType = $this->getAttr("reportType", 'HQPReport');
        $useProject = $this->getAttr("project", false);
        $buttonName = $this->getAttr("buttonName", "Report PDF");
        $noRenderIfNull = $this->getAttr("noRenderIfNull", "false");
        $year = $this->getAttr("year", $this->getReport()->year);
        $width = $this->getAttr("width", 'auto');
        if(strstr($width, "%") !== false){
            $width = $width.";-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box";
        }
        $project = null;
        if($useProject){
            $project = Project::newFromHistoricId($this->projectId);
            if($project == null && $this->projectId > 0){
                $project = new Project(array());
                $project->id = $this->projectId;
            }
        }
        $person = Person::newFromId($this->personId);
        $projects = array();
        if($project != null){
            $projects = array_merge(array($project), $project->getPreds());
        }
        $found = false;
        foreach($projects as $project){
            $report = new DummyReport($reportType, $person, $project, $year, true);
            if($report->allowIdProjects){
                // Handle allowIdProjects
                $report->project = new Project(array());
                $report->project->id = $this->projectId;
            }
            $tok = false;
            $tst = '';
            $len = 0;
            $sub = 0;
            $sto = new ReportStorage($person, $project);
        	$check = $report->getPDF();
        	if (count($check) > 0 && ($reportType != "ProjectNIComments" || $person->getId() != $me->getId())) {
        		$tok = $check[0]['token'];
        		$sto->select_report($tok);
        		$tst = $sto->metadata('timestamp');
        		$len = $sto->metadata('len_pdf');
        		$sub = $sto->metadata('submitted');
        		$item = "<a class='button' style='width:{$width};' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}'>{$buttonName}</a>";
        		$item = $this->processCData($item);
		        $wgOut->addHTML($item);
		        $found = true;
		        break;
        	}
        }
        if(!$found){
            if($noRenderIfNull == "true"){
    	        return;
    	    }
    	    $wgOut->addHTML($this->processCData(""));
        }
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
