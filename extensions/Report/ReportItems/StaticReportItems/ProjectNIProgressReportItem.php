<?php

class ProjectNIProgressReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "<div id='{$this->personId}_progress_details'>$details</div>";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getTableHTML(){
	    $person = Person::newFromId($this->personId);
        $project = Project::newFromId($this->projectId);
        $reportItemSet = new ProjectPeopleReportItemSet();
        $reportItemSet->setPersonId($this->personId);
		$reportItemSet->setProjectId($this->projectId);
		$reportItemSet->setMilestoneId($this->milestoneId);
		$reportItemSet->setProductId($this->productId);
		$people = $reportItemSet->getData();
        $nPeople = count($people);
        $nSubmitted = 0;
        $details = "";
	    foreach($people as $p){
	        $pers = Person::newFromId($p['person_id']);
	        $report = new DummyReport('NIReport', $pers, $project);
            if($report->isSubmitted()){
                $nSubmitted++;
            }
        }
        
        // Budgets
        $allocatedBudget = $project->getAllocatedBudget(REPORTING_YEAR-1);
		$requestedBudget = $project->getRequestedBudget(REPORTING_YEAR);
        
        $nAllocated = 0;
        $nRequested = 0;
        $nPlansForward = 0;
		foreach($people as $p){
		    $pers = Person::newFromId($p['person_id']);
            $allocBudget = $allocatedBudget->copy()->select(V_PERS_NOT_NULL, array($pers->getReversedName()));
            if(($allocBudget->nRows() * $allocBudget->nCols()) > 0){
                $nAllocated++;
            }
            $reqBudget = $requestedBudget->copy()->select(V_PERS_NOT_NULL, array($pers->getReversedName()));
            if(($reqBudget->nRows() * $reqBudget->nCols()) > 0){
                $nRequested++;
            }
            $addr = ReportBlob::create_address(RP_RESEARCHER, RES_RESACTIVITY, RES_RESACT_NEXTPLANS, 0);
            $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $pers->getId(), $project->getId());
            $blob->load($addr);
            $data = $blob->getData();
            if($data == ""){
                $nPlansForward++;
            }
        }
        $error = "";
        if($project->isDeleted() || $project->getPhase() < PROJECT_PHASE && $nRequested > 0){
            $error = "class='inlineError'";
        }
        $rowspan = 3;
        if($project->getPhase() == PROJECT_PHASE){
            $rowspan = 4;
        }
        $details .= "<tr><td style='white-space:nowrap;' valign='top' rowspan='$rowspan'><b>NI Progress</b></td><td>{$nSubmitted} of the {$nPeople} NIs have submitted their reports\n</td></tr>";
        if($project->getPhase() == 1){ // TODO: Change this for 2014 reporting
            $details .= "<tr><td>{$nAllocated} of the {$nPeople} NIs have uploaded a revised budget for ".$this->getReport()->year." allocated funds\n</td></tr>";
        }
        $details .= "<tr><td><span $error>{$nRequested} of the {$nPeople} NIs have uploaded a budget request</span>\n</td></tr>";
        if($project->getPhase() == PROJECT_PHASE){
            $details .= "<tr><td>{$nPlansForward} of the {$nPeople} NIs have not filled in their \"plans forward\" narrative for this project\n</td></tr>";
        }
        return $details;
	}
}

?>
