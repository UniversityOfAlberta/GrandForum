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
        $allocatedBudget = $project->getAllocatedBudget(REPORTING_YEAR);
		$requestedBudget = $project->getRequestedBudget(REPORTING_YEAR);
        
        $nAllocated = 0;
        $nRequested = 0;

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
        }
        $error = "";
        if($project->isDeleted() && $nRequested > 0){
            $error = "class='inlineError'";
        }
        $details .= "<tr><td style='white-space:nowrap;' valign='top' rowspan='3'><b>NI Progress</b></td><td>{$nSubmitted} of the {$nPeople} NIs have submitted their reports\n</td></tr>";
        $details .= "<tr><td>{$nAllocated} of the {$nPeople} NIs have uploaded a revised budget for 2012 allocated funds\n</td></tr>";
        $details .= "<tr><td><span $error>{$nRequested} of the {$nPeople} NIs have uploaded a budget request</span>\n</td></tr>";
        return $details;
	}
}

?>
