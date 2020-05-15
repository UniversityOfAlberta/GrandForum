<?php

class SeeReportReportItem extends StaticReportItem {

	function render(){
        global $wgOut, $wgServer, $wgScriptPath, $wgImpersonating;
        $person = Person::newFromId($this->personId);
        $useProject = $this->getAttr("project", false);
        $url = $_SERVER["REQUEST_URI"];
        $reportType = $this->getAttr("reportType", "HQPReport");
        $width = $this->getAttr("width", 'auto');
        $project = $this->getReport()->project;
        if($project != null){
            $url = str_replace("&project={$project->getName()}", "", $url);
        }
        if($useProject == "true"){
            $project = Project::newFromHistoricId($this->projectId);
            if($project != null){
                $url = $url."&project={$project->getName()}";
            }
        }
        $url = str_replace("&showSection", "", str_replace("report={$this->getReport()->xmlName}", "report={$reportType}", $url));
        if(!$wgImpersonating){
            $item = "<a class='button' style='width:$width;' target='_blank' href='$url&impersonate={$person->getName()}'>On-Line Report</a>";
        }
        else{
            $item = "<a class='disabledButton' style='width:$width;'>On-Line Report</a>";
        }
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
