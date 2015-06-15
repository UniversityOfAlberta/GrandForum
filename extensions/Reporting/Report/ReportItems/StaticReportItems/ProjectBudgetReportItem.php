<?php

class ProjectBudgetReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $project = Project::newFromId($this->projectId);
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $year = $this->getReport()->year;
        
        $people = $project->getAllPeopleDuring(null, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH);
        $nis = array("");
        foreach($people as $person){
            if($person->isRoleDuring(NI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
                $nis[] = $person->getReversedName();
            }
        }
        
        $NIBudget = $budget->copy();
        
        $totalBudget = $budget->copy()->uncube()->rasterize()->filter(HEAD1, array("Name of%"))->cube()->rasterize()->filterCols(CUBE_COL_TOTAL);
        
        $nitotal = intval(str_replace("$", "", $NIBudget->copy()->rasterize()->select(CUBE_TOTAL)->where(CUBE_TOTAL)->toString()));
        $total = $nitotal;
        
        $wgOut->addHTML("<h2>NI Budget Requests</h2><div>");
        $wgOut->addHTML($NIBudget->render());
        $wgOut->addHTML("</div><h2>Totals</h2><div>");
        $wgOut->addHTML($totalBudget->render());
        $wgOut->addHTML("</div>");
    }
    
    function renderForPDF(){
        global $wgOut;
        $wgOut->addHTML("<div>");
        $project = Project::newFromId($this->projectId);
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $year = $this->getReport()->year;
        
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
                               ->filter(HEAD1, array("Budget Categories for %", 
                                                     "1) %",
                                                     "2) %",
                                                     "5) %"));
        
        $people = $project->getAllPeopleDuring(null, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH);
        $nis = array("");
        foreach($people as $person){
            if($person->isRoleDuring(NI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
                $nis[] = $person->getReversedName();
            }
        }
        
        $total_html = $copy->copy()
                             ->uncube()
                             ->rasterize()
                             ->filter(HEAD1, array("Name of%"))
                             ->cube()
                             ->rasterize()
                             ->filterCols(CUBE_COL_TOTAL)
                             ->transpose()
                             ->renderForPDF();
        
        $NIBudget = $copy->copy();
                                                     
        $nibudget_html = $NIBudget->transpose()
                                  ->renderForPDF();
        $new_nibudget = new SmartDomDocument();
        $new_totalbudget = new SmartDomDocument();
        $new_nibudget->loadHTML($nibudget_html);
        $new_totalbudget->loadHTML($total_html);
        foreach($new_nibudget->getElementsByTagName("table") as $table){
            if($table->getAttribute('id') == "budget"){
                $tr = $table->getElementsByTagName("tr")->item(0);
                foreach($tr->getElementsByTagName("b") as $b){
                    $b->nodeValue = (isset($budget_legend[$b->nodeValue]))? $budget_legend[$b->nodeValue] : $b->nodeValue;
                }
            }
        }
        foreach($new_totalbudget->getElementsByTagName("table") as $table){
            if($table->getAttribute('id') == "budget"){
                $tr = $table->getElementsByTagName("tr")->item(0);
                foreach($tr->getElementsByTagName("b") as $b){
                    $b->nodeValue = (isset($budget_legend[$b->nodeValue]))? $budget_legend[$b->nodeValue] : $b->nodeValue;
                }
            }
        }
        
        $nitotal = intval(str_replace("$", "", $NIBudget->copy()->rasterize()->select(CUBE_TOTAL)->where(CUBE_TOTAL)->toString()));
        $total = $nitotal;
        
        $wgOut->addHTML("<h2>NI Budget Requests</h2><div>");
        $wgOut->addHTML($new_nibudget);
        $wgOut->addHTML("</div><h2>Totals</h2><div>");
        $wgOut->addHTML("$new_totalbudget");
        $wgOut->addHTML($budget_legend_html);
    }
}

?>
