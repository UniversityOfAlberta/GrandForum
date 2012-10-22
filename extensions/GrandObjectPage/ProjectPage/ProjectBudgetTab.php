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
            $budget = $project->getRequestedBudget(REPORTING_YEAR);
            if($budget->nRows()*$budget->nCols() <= 6){
                $budget = $project->getRequestedBudget(REPORTING_YEAR-1);
            }
            if($budget != null){
                $this->html .= "<h2><span class='mw-headline'>Budget</span></h2>";
            }
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= $budget->render();
            $this->html .="</div>";
        }
    }
}    
    
?>
