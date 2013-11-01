<?php

class ProjectBudgetReportItem extends StaticReportItem {

	function render(){
		global $wgOut;
		$project = Project::newFromId($this->projectId);
		$budget = $project->getRequestedBudget(REPORTING_YEAR);
		
		$people = $project->getAllPeopleDuring(null, REPORTING_CYCLE_START, REPORTING_CYCLE_END);
		$pnis = array("");
		$cnis = array("");
		foreach($people as $person){
		    if($person->isRoleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		        $pnis[] = $person->getReversedName();
		    }
		    else if($person->isRoleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		        $cnis[] = $person->getReversedName();
		    }
		}
		
		$PNIBudget = $budget->copy()->uncube()->filterCols(V_PERS_NOT_NULL, $cnis)->cube();
		$CNIBudget = $budget->copy()->uncube()->filterCols(V_PERS_NOT_NULL, $pnis)->cube();
		$wgOut->addHTML("<h2>PNI Budget Requests</h2><div>");
		$wgOut->addHTML($PNIBudget->render());
		$wgOut->addHTML("</div><h2>CNI Budget Requests</h2><div>");
		$wgOut->addHTML($CNIBudget->render());
		$wgOut->addHTML("</div>");
	}
	
	function renderForPDF(){
	    global $wgOut;
		$wgOut->addHTML("<div>");
		$project = Project::newFromId($this->projectId);
		$budget = $project->getRequestedBudget(REPORTING_YEAR);
		$budget_legend = array(
            "Name of network investigator submitting request:" => "Name of NI",
            "1) Salaries and stipends" => "",
            "a) Graduate students" => "1a)",
            "b) Postdoctoral fellows" => "1b)",
            "c) Technical and professional assistants" => "1c)",
            "d) Undergraduate students" => "1d)",
            "2) Equipment" => "",
            "a) Purchase or rental" => "2a)",
            "b) Maintenance costs" => "2b)",
            "c) Operating costs" => "2c)",
            "3) Materials and supplies" => "3)",
            "4) Computing costs" => "4)",
            "5) Travel expenses" => "",
            "a) Field trips" => "5a)",
            "b) Conferences" => "5b)",
            "c) GRAND annual conference" => "5c)"
        );
        
        $budget_legend_html = "<h3>Table Legend:</h3><div>";
        foreach ($budget_legend as $i => $j){
            if($i == "Name of network investigator submitting request:"){
                continue;
            }
            if($i == "Budget Categories for April 1, 2012, to March 31, 2013"){
                $i = "* Budget Categories for April 1, 2012, to March 31, 2013";
            }
            if($i == "1) Salaries and stipends" ){
                $budget_legend_html .= "<div>$i<div style='padding-left:14px;'>";
            }
            else if( $i == "d) Undergraduate students" ){
                $budget_legend_html .= "<div>$i</div></div></div>";
            }
            else if($i == "2) Equipment" ){
                $budget_legend_html .= "<div>$i<div style='padding-left:14px;'>";
            }
            else if( $i == "c) Operating costs" ){
                $budget_legend_html .= "<div>$i</div></div></div>";
            } 
            else if($i == "5) Travel expenses" ){
                $budget_legend_html .= "<div>$i<div style='padding-left:14px;'>";
            }
            else if( $i == "c) GRAND annual conference" ){
                $budget_legend_html .= "<div>$i</div></div></div>";
            }   
            else{
                $budget_legend_html .= "<div>$i</div>";
            }
        }
        $budget_legend_html .= "</div>";
        $copy = $budget->copy()->rasterize()
                               ->filter(HEAD1, array("Budget Categories for April 1, ".(REPORTING_YEAR+1).", to March 31, ".(REPORTING_YEAR+2), 
                                                     "1) Salaries and stipends",
                                                     "2) Equipment",
                                                     "5) Travel expenses"));
        
        $people = $project->getAllPeopleDuring(null, REPORTING_CYCLE_START, REPORTING_CYCLE_END);
		$pnis = array("");
		$cnis = array("");
		foreach($people as $person){
		    if($person->isRoleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		        $pnis[] = $person->getReversedName();
		    }
		    else if($person->isRoleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		        $cnis[] = $person->getReversedName();
		    }
		}
		
		$PNIBudget = $copy->copy()->uncube()->filterCols(V_PERS_NOT_NULL, $cnis)->cube();
		$CNIBudget = $copy->copy()->uncube()->filterCols(V_PERS_NOT_NULL, $pnis)->cube();
                                                     
		$pnibudget_html = $PNIBudget->transpose()
                                    ->renderForPDF();
        $cnibudget_html = $CNIBudget->transpose()
                                    ->renderForPDF();
        $new_pnibudget = new SmartDomDocument();
        $new_cnibudget = new SmartDomDocument();
        $new_pnibudget->loadHTML($pnibudget_html);
        $new_cnibudget->loadHTML($cnibudget_html);
        foreach($new_pnibudget->getElementsByTagName("table") as $table){
            if($table->getAttribute('id') == "budget"){
                $tr = $table->getElementsByTagName("tr")->item(0);
                foreach($tr->getElementsByTagName("b") as $b){
                    $b->nodeValue = (isset($budget_legend[$b->nodeValue]))? $budget_legend[$b->nodeValue] : $b->nodeValue;
                }
            }
        }
        foreach($new_cnibudget->getElementsByTagName("table") as $table){
            if($table->getAttribute('id') == "budget"){
                $tr = $table->getElementsByTagName("tr")->item(0);
                foreach($tr->getElementsByTagName("b") as $b){
                    $b->nodeValue = (isset($budget_legend[$b->nodeValue]))? $budget_legend[$b->nodeValue] : $b->nodeValue;
                }
            }
        }
        $wgOut->addHTML("<h2>PNI Budget Requests</h2><div>");
        $wgOut->addHTML($new_pnibudget);
        $wgOut->addHTML("</div><h2>CNI Budget Requests</h2><div>");
        $wgOut->addHTML($new_cnibudget);
		$wgOut->addHTML($budget_legend_html);
		$wgOut->addHTML("</div>");
	}
}

?>
