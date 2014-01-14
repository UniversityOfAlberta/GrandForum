<?php

class EvalBudgetReportItem extends AbstractReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		$person_attr = $this->getAttr("person", "false");
		$project_attr = $this->getAttr("project", "false");
		$revised = "";
		if($person_attr == "true"){ 
			$person = Person::newFromId($this->personId);
	        $name = $person->getName();
	        $read_name = $person->getReversedName();
	        $budget = $person->getRequestedBudget(REPORTING_YEAR);
		}
		else if($project_attr == "true"){
			$project = Project::newFromId($this->projectId);
			$name = $read_name = $project->getName();
			$budget = $project->getRequestedBudget(REPORTING_YEAR);
			$revised = $project->getRevisedBudget(REPORTING_YEAR);
		}
		
        if($budget instanceof Budget){
            if($person_attr == "true"){
            	$budget = $budget->filterCols(V_PROJ, array(""))->render();
            }
            else if($project_attr == "true"){
            	$budget = $budget->render();
            	if($revised != null){
            	    $revised = "<h3>Revised Budget</h3>The following is a revised budget which was uploaded after the reports were closed for editing.<br />".$revised->render();
            	}
            }
            $budget_lbl = "<span style='color:green;'>Budget Preview</span>";
        }
        else{
            $budget = "<p>No Budget Found</p>";
            $budget_lbl  = "<span style='color:red;'>No Budget Found</span>";
        }
        $wgOut->addHTML("<div class='pni_budget_accordions'><h2>{$read_name}: {$budget_lbl}<span style='font-size:60%; float:right;''><a href=''>(Click to Show/Hide)</a></span></h2>");
		
		$wgOut->addHTML("<div id='{$name}_budgetDiv'>");
        
        $wgOut->addHTML($budget);
        $wgOut->addHTML($revised);
        
		$wgOut->addHTML("</div></div>");
	}
}

?>
