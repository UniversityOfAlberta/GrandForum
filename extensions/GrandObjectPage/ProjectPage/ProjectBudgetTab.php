<?php

class ProjectBudgetTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectBudgetTab($project, $visibility){
        parent::AbstractTab("Budget");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        $edit = $this->visibility['edit'];
        
        if($edit){
            //$this->html .= "<form class='autosave' action='{$project->getUrl()}?edit' method='post'>";
        }
        
        $this->showBudget();
        
        
        if($edit){
            //$this->html .= "</form>";
        }
        
        return $this->html;
    }

    function showBudget(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;
        $edit = $this->visibility['edit'];
        $isLead = $this->visibility['isLead'];
        $project = $this->project;
        
        if($isLead){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#budgetAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
            //$this->html .= "<h2><span class='mw-headline'>Budgets</span></h2>";
            $this->html .= "<div id='budgetAccordion'>";
            for($i=REPORTING_YEAR; $i >= 2011; $i--){
                $this->html .= "<h3><a href='#'>".$i."</a></h3>";
                $this->html .= "<div style='overflow: auto;'>";

                $budget = $project->getAllocatedBudget($i-1);
                //if($i==REPORTING_YEAR && $budget->nRows()*$budget->nCols() <= 6){
                //    $budget = $project->getRequestedBudget($i-1);
                //}
                if($budget != null){
                    $this->html .= $budget->render();
                }
                else {
                    $this->html .= "No budget could be found for $i";
                }
                $this->html .="</div>";
            }
            $this->html .= "</div>";

        }
    }
}    
    
?>
