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
        $reportItemSet = new ProjectPeopleNoLeadersReportItemSet();
        $reportItemSet->setPersonId($this->personId);
		$reportItemSet->setProjectId($this->projectId);
		$reportItemSet->setMilestoneId($this->milestoneId);
		$reportItemSet->setProductId($this->productId);
		$people = $reportItemSet->getData();
        $nPeople = count($people);
        $nSubmitted = 0;
        $doneAlready = array();
        $details = "";
	    foreach($people as $p){
	        $pers = Person::newFromId($p['person_id']);
	        $report = new DummyReport('NIReport', $pers, $project);
            if($report->isSubmitted()){
                $nSubmitted++;
            }
        }
        $details .= "<tr><td style='white-space:nowrap;'><b>NI Progress</b></td><td>{$nSubmitted} of the {$nPeople} NIs have submitted their reports\n</td></tr>";
        return $details;
	}
}

?>
