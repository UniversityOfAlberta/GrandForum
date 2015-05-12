<?php

class NIProgressReportItem extends StaticReportItem {

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
        $project = $this->getReport()->project;
        
	    $reportType = $this->getAttr('reportType', 'NIReport');
        $report = new DummyReport($reportType, $person, $project);
        if($project != null){
            $projects = array($project);
        }
        else{
            $projects = $person->getProjectsDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        }
        
        $errorMsg = "";
        $rowspan = 0;
        if($reportType == "NIReport"){
            $budget = null;
            $rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_BUDGET, 0, 0);
            $budget_blob = new ReportBlob(BLOB_EXCEL, REPORTING_YEAR, $person->getId(), 0);
            $budget_blob->load($rep_addr);
            $budgetData = $budget_blob->getData();
            if($budgetData != null){
                $budget = new Budget("XLS", REPORT2_STRUCTURE, $budgetData);
                $budget->filterCols(V_PROJ, array(""));
                $errors = BudgetReportItem::checkDeletedProjects($budget, $person, $this->getReport()->year);
                BudgetReportItem::checkTotals($budget, $person, $this->getReport()->year);
                foreach($errors as $key => $error){
                    $budget->errors[0][] = $error;
                }
            }
            if($budget == null || $budget->isError()){
                $rowspan++;
            }
            
            if($rowspan > 0){
                $errorMsg .= "<tr><td rowspan='$rowspan'><b>Budget</b></td>";
            }

            if($budget == null){
                $errorMsg .= "<td><span class='inlineError'>You have not uploaded a budget request</span></td></tr>\n";
            }
            else{
		        if($budget->isError()){
		            $errors = $budget->showErrorsSimple();
		            $errors = str_replace("<br />", "</span><br /><span class='inlineError'>", $errors);
                    $errors = str_replace("<br /><span class='inlineError'></span>", "", $errors);
                    $errorMsg .= "<td><span class='inlineError'>$errors</span></td></tr>\n";
                }
            }
        }
        
        $details = "";
        
        $details .= "$errorMsg</td></tr>";
        return $details;
	}
}

?>
