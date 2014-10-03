<?php

class SmallNIBudgetReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        $person = Person::newFromId($this->personId);
        
        $start = intval($this->getAttr("start", "0000"));
        $end = intval($this->getAttr("end", REPORTING_YEAR));
        $item = "<table cellspacing='0' cellpadding='0'><tr><th>&nbsp;Funding Year&nbsp;</th><th>&nbsp;Requested&nbsp;</th><th>&nbsp;Allocated&nbsp;</th></tr>";
        for($i = $start; $i <= $end; $i++){
            $iS = $i+1;
            $iE = $i+2;
            
            $requested = $person->getRequestedBudget($i);
            $allocated = $person->getAllocatedBudget($i+1);
            
            $rAmnt = "$0";
            $aAmnt = "$0";
            if($requested != null){
                $rAmnt = '$'.@number_format(str_replace("$", "", $requested->copy()->rasterize()->where(HEAD1, array("TOTALS%"))->select(ROW_TOTAL)->toString()), 0);
            }
            if($allocated != null){
                $aAmnt = '$'.@number_format(str_replace("$", "", $allocated->copy()->rasterize()->where(HEAD1, array("TOTALS%"))->select(ROW_TOTAL)->toString()), 0);
            }
            
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
        return $item;
    }

    function render(){
        global $wgOut;
        $item = $this->getHTML();
        $item = str_replace("cellspacing='0'", "cellspacing='1'", $item);
        $item = str_replace("cellpadding='0'", "cellpadding='1'", $item);
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
