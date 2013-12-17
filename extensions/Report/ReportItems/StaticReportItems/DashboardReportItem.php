<?php

class DashboardReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgUser;
		$table = ($this->getAttr("table", "true") == "true");
		$details = ($this->getAttr("details", "true") == "true");
		$limit = $this->getAttr("limit", "0");
        $dashboard = $this->createDashboard();
        $dashboard = $this->filterRows($dashboard);
        $dashboard = $this->filterCols($dashboard);
        $dash = "";
        if($limit > 0){
            $top = $dashboard->copy()->limit(0, 1);
            for($i = 1; $i < $dashboard->nRows(); $i+=$limit){
                $dash .= $top->copy()->union($dashboard->copy()->limit($i, $limit))->render();
            }
        }
        else{
            $dash = $dashboard->render();
        }
        $item = $this->processCData($dash);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut, $wgUser;
	    $table = ($this->getAttr("table", "true") == "true");
		$details = ($this->getAttr("details", "true") == "true");
		$limit = $this->getAttr("limit", "0");
		$totalOnly = ($this->getAttr("totalOnly", "false") == "true");
	    $dashboard = $this->createDashboard();
        $dashboard = $this->filterRows($dashboard);
        $dashboard = $this->filterCols($dashboard);
        if($totalOnly){
            $top = $dashboard->copy()->limit(0, 1);
            if(!$this->getReport()->topProjectOnly){
                $dashboard = $top->copy()->union($dashboard->copy()->where(HEAD, array("Total:"))->union($dashboard->copy()->where(HEAD, array("Total:"))->limit(1,1)));
            }
            else{
                $dashboard = $top->copy()->union($dashboard->copy()->limit(1,1)->union($dashboard->copy()->limit(1,1))->union($dashboard->copy()->limit(1,1)) );
            }
            $dashboard->filterCols(HEAD, array("Projects"));
        }
		$dash = "";
        if($limit > 0){
            $top = $dashboard->copy()->limit(0, 1);
            for($i = 1; $i < $dashboard->nRows(); $i+=$limit){
                $dash .= $top->copy()->union($dashboard->copy()->limit($i, $limit))->renderForPDF($table, $details);
            }
        }
        else{
            $dash = $dashboard->renderForPDF($table, $details);
        }
        
        $item = $this->processCData($dash);
		$wgOut->addHTML($item);
	}
	
	function createDashboard(){
	    $person = $this->getReport()->person;
	    $project = Project::newFromId($this->projectId);
	    $struct = constant($this->getAttr("structure", "NI_REPORT_STRUCTURE"));
		if($project != null && $struct >= PROJECT_PUBLIC_STRUCTURE){
            $dashboard = new DashboardTable($struct, $project);
        }
        else{
            $dashboard = new DashboardTable($struct, $person);
        }
        if($project != null && $project->getName() != null && 
           substr($project->getEffectiveDate(), 0, 4) == REPORTING_YEAR &&
           $struct == PROJECT_REPORT_TIME_STRUCTURE){
            $dashboard->filterCols(HEAD, array("Requested.*"));
        }
        return $dashboard;
	}
	
	function filterRows($dashboard){
	    if($this->getReport()->topProjectOnly){
	        $person = $this->getReport()->person;
	        $project = $this->getReport()->project;
            $dashboard = $dashboard->copy();
            $dashboard->filter(HEAD, array('Total:'));
            foreach($person->getProjects() as $proj){
                if($proj->getId() != $project->getId()){
                    $dashboard = $dashboard->filter(PERSON_PROJECTS, array($proj->getName()));
                }
            }
        }
        return $dashboard;
	}
	
	function filterCols($dashboard){
	    if($this->getAttr("structure") == "PROJECT_REPORT_TIME_STRUCTURE" && $this->getReport()->project != null){
	        $created = $this->getReport()->project->getCreated();
	        if($created > $this->getReport()->year.REPORTING_CYCLE_END_MONTH){
	            $dashboard = $dashboard->copy()->filterCols(HEAD, array("Hours%", "Allocated%"));
	        }
	    }
	    return $dashboard;
	}

}

?>
