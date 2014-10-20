<?php

class SmallProjectBudgetReportItem extends StaticReportItem {

    function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $project = Project::newFromId($this->projectId);
        
        $start = intval($this->getAttr("start", "0000"));
        $end = intval($this->getAttr("end", REPORTING_YEAR));
        $item = "<table cellspacing='0' cellpadding='0'><tr><th>&nbsp;Funding Year&nbsp;</th><th>&nbsp;Requested&nbsp;</th><th>&nbsp;Allocated&nbsp;</th></tr>";
        for($i = $start; $i <= $end; $i++){
            $iS = $i+1;
            $iE = $i+2;
            
            $requested = $project->getRequestedBudget($i);
            $allocated = $project->getAllocatedAmount($i+1);
            
            $rAmnt = '$'.@number_format(str_replace("$", "", $requested->copy()->rasterize()->where(CUBE_TOTAL)->select(CUBE_TOTAL)->toString()), 0);
            $aAmnt = '$'.@number_format($allocated, 0);
            
            if($rAmnt == '$0' || $rAmnt == '$'){
                $rAmnt = "N/A";
            }
            if($aAmnt == '$0' || $aAmnt == '$'){
                if($i == REPORTING_YEAR){
                    $aAmnt = "";
                }
                else{
                    $aAmnt = "N/A";
                }
            }
            
            $item .= "<tr><td align='center'>Apr {$iS} to Mar {$iE}</td><td align='right'>$rAmnt</td><td align='right'>$aAmnt</td></tr>";
        }
        $item .= "</table>";
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        $this->render();
    }
}

?>
