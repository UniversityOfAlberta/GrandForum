<?php

class SupervisorProgressReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
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
        $reportItemSet = new PersonSupervisesReportItemSet();
        $reportItemSet->setPersonId($this->personId);
		$reportItemSet->setProjectId($this->projectId);
		$reportItemSet->setMilestoneId($this->milestoneId);
		$reportItemSet->setProductId($this->milestoneId);
        $hqps = $reportItemSet->getData();
        $nHqps = count($hqps);
        $nSubmitted = 0;
        $doneAlready = array();
        $details = "";
	    foreach($hqps as $h){
	        $hqp = Person::newFromId($h['person_id']);
	        $report = new DummyReport('HQPReport', $hqp, $project);
            if($report->isSubmitted()){
                $nSubmitted++;
            }
        }
        $details .= "<tr><td><b>HQP</b></td><td>{$nSubmitted} of {$nHqps} ".Inflect::smart_pluralize($nHqps, "HQP")." ".Inflect::smart_pluralize($nSubmitted, "has")." submitted their ".Inflect::smart_pluralize($nSubmitted, "report")."\n</td></tr>";
        return $details;
	}
}

?>
