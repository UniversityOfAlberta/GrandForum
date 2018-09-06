<?php

class DashboardReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgUser;
		$table = ($this->getAttr("table", "true") == "true");
		$details = ($this->getAttr("details", "true") == "true");
		$limit = $this->getAttr("limit", "0");
		if($table == false){
		    $this->renderForPDF();
		    return;
		}
        $dashboard = $this->createDashboard();
        $dashboard = $this->filterCols($dashboard);
        $dashboard = $this->filterRows($dashboard);
        $dashboard = $this->splitCompleted($dashboard);
        $dash = "";
        if($limit > 0){
            $top = $dashboard->copy()->limit(0, 1);
            for($i = 1; $i < $dashboard->nRows(); $i+=$limit){
                $dash .= $top->copy()->union($dashboard->copy()->limit($i, $limit))->render();
            }
        }
        else{
            $dash = $dashboard->render(false, true);
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
        $dashboard = $this->filterCols($dashboard);
        $dashboard = $this->filterRows($dashboard);
        $dashboard = $this->splitCompleted($dashboard);
        if($totalOnly){
            $top = $dashboard->copy()->limit(0, 1);
            if(!$this->getReport()->topProjectOnly){
                $dashboard = $top->copy()->union($dashboard->copy()->where(HEAD, array("Total:"))->union($dashboard->copy()->where(HEAD, array("Total:"))->limit(1,1)));
            }
            else{
                $dashboard = $top->copy()->union($dashboard->copy()->limit(1,1)->union($dashboard->copy()->limit(1,1))->union($dashboard->copy()->limit(1,1)) );
            }
            $dashboard->filterCols(HEAD, array("Projects"));
            $dashboard->filterCols(HEAD, array("People"));
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
	    $person = Person::newFromId($this->personId);
	    $project = Project::newFromId($this->projectId);
	    $struct = constant($this->getAttr("structure", "NI_REPORT_STRUCTURE"));
	    $tableType = strtolower($this->getAttr("tableType", ""));
		if(($project != null && $struct >= PROJECT_PUBLIC_STRUCTURE) && $tableType != "person"){
            $dashboard = new DashboardTable($struct, $project);
        }
        else {
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
        }
        return $dashboard;
	}
	
	function filterCols($dashboard){
	    if($this->getAttr("structure") == "PROJECT_REPORT_TIME_STRUCTURE" && $this->getReport()->project != null){
	        $created = $this->getReport()->project->getCreated();
	        if($created > ($this->getReport()->year+1).REPORTING_CYCLE_END_MONTH){
	            $dashboard = $dashboard->copy()->filterCols(HEAD, array("Hours%", "Allocated%"));
	        }
	    }
	    return $dashboard;
	}
	
	function splitCompleted($dashboard){
	    $completed = $dashboard->copy()->where(PERSON_PROJECTS, array("%Completed%"));
	    $rest = $dashboard->copy()->filter(PERSON_PROJECTS, array("%Completed%"));
	    $last = $rest->copy()->limit($rest->nRows()-1, 1);
	    $rest->limit(0, $rest->nRows()-1);
	    if($completed->nRows() > 0){
	        $rest->union(new DashboardTable(array(array(HEAD)),
                                             array(array("Phase 1 Projects")), null));
	        $rest->union($completed);
	    }
	    $rest->union($last);
	    return $rest;
	}

}

?>
