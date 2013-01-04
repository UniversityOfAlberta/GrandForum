<?php

class SubBudgetPDFReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $wgOut->addHTML("<h2>Budget</h2>");
        $wgOut->addHTML("<div>");
        $project = Project::newFromId($this->projectId);
        $budget = $project->getRequestedBudget(REPORTING_YEAR);
        $wgOut->addHTML($budget->render());
        $wgOut->addHTML("</div>");
    }
    
    function renderForPDF(){
        global $wgOut;
        $wgOut->addHTML("<h2>Budget</h2>");
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
        $budget_html = $budget->copy()->rasterize()
                                ->filter(HEAD1, array("Budget Categories for April 1, ".(REPORTING_YEAR+1).", to March 31, ".(REPORTING_YEAR+2), 
                                                    "1) Salaries and stipends",
                                                    "2) Equipment",
                                                    "5) Travel expenses"))
                                ->transpose()
                                ->renderForPDF();
        $new_budget = new SmartDomDocument();
        $new_budget->loadHTML($budget_html);
        foreach($new_budget->getElementsByTagName("table") as $table){
            if($table->getAttribute('id') == "budget"){
                $tr = $table->getElementsByTagName("tr")->item(0);
                foreach($tr->getElementsByTagName("b") as $b){
                    $b->nodeValue = (isset($budget_legend[$b->nodeValue]))? $budget_legend[$b->nodeValue] : $b->nodeValue;
                }
            }
        }
        $wgOut->addHTML($new_budget);
        $wgOut->addHTML($budget_legend_html);
        $wgOut->addHTML("</div>");
    }
}

?>
