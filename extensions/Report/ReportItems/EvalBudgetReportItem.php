<?php

class EvalBudgetReportItem extends AbstractReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		/*if(isset($_GET['downloadBudget'])){
		    $data = $this->getBlobValue();
		    if($data != null){
		        $person = Person::newFromId($wgUser->getId());
		        header('Content-Type: application/vnd.ms-excel');
		        header("Content-disposition: attachment; filename='{$person->getNameForForms()}_Budget.xls'");
		        echo $data;
		        exit;
		    }
		}*/
		$person_attr = $this->getAttr("person", "false");
		$project_attr = $this->getAttr("project", "false");
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
			
		}
		
		
        if($budget instanceof Budget){
            if($person_attr == "true"){
            	$budget = $budget->filterCols(V_PROJ, array(""))->render();
            }
            else if($project_attr == "true"){
            	$budget = $budget->render();
            }

            $budget_lbl = "<span style='color:green;'>Budget Preview</span>";
        }
        else{
            $budget = "<p>No Budget Found</p>";
            $budget_lbl  = "<span style='color:red;'>No Budget Found</span>";
        }
        $wgOut->addHTML("<div class='pni_budget_accordions'><h2>{$read_name}: {$budget_lbl}</h2>");
		
		$wgOut->addHTML("<div id='{$name}_budgetDiv'>");
        
        $wgOut->addHTML($budget);
        
		$wgOut->addHTML("</div></div>");
	}
	
    /*
	function renderForPDF(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        $data = $this->getBlobValue();
		if($data !== null){
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $budget = $this->filterCols($budget);
		    $wgOut->addHTML($budget->copy()->filterCols(V_PROJ, array(""))->renderForPDF());
		}
		else{
		    $wgOut->addHTML("You have not yet uploaded a budget");
		}
	}
	
	
	
	function filterCols($budget){
	    if($this->getReport()->topProjectOnly){
	        $person = $this->getReport()->person;
	        $project = $this->getReport()->project;
            $budget = $budget->copy();
            $personRow = $budget->copy()->where(HEAD1, array("Name of network investigator submitting request:"));
            foreach(Project::getAllProjects() as $proj){
                if($proj->getId() != $project->getId()){
                    $budget = $budget->filterCols(V_PROJ, array($proj->getName()));
                }
            }
            $personRow->limitCols(0, $budget->nCols());
            $budget = $budget->filter(HEAD1, array("Name of network investigator submitting request:"));
            $budget = $personRow->union($budget);
        }
        return $budget;
	}
	*/
}

?>
